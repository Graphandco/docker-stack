# Configuration du Routage Dynamique de Stockage (Folder-to-Bucket)

Ce document détaille la procédure complète pour associer dynamiquement un dossier de la médiathèque Directus à un bucket S3/SeaweedFS spécifique (par exemple, un bucket public).

---

## 🏗️ 1. Principe de fonctionnement

Par défaut, Directus envoie tous les nouveaux fichiers sur l'emplacement de stockage principal (sixmyk).

Pour diriger automatiquement les fichiers d'un dossier vers un bucket dédié (public) :
1. Directus enregistre le fichier sur le bucket principal (sixmyk).
2. L'extension Hook storage-router intercepte l'événement d'upload (files.upload).
3. Si le fichier est téléversé dans le dossier ciblé :
   - L'extension effectue un transfert direct entre buckets (sixmyk vers public) au niveau de SeaweedFS/S3.
   - Elle supprime la copie sur le bucket source (sixmyk).
   - Elle met à jour le champ storage dans la base de données PostgreSQL pour que Directus sache que le fichier réside sur public.

---

## ⚙️ 2. Configuration des Variables d'Environnement (.env)

Dans le fichier .env situé à la racine de ton instance Directus, déclare les deux emplacements de stockage (STORAGE_LOCATIONS) et leurs identifiants respectifs :

STORAGE_LOCATIONS="sixmyk, public"

# --- BUCKET PRIVÉ / PRINCIPAL (SIXMYK) ---
STORAGE_SIXMYK_DRIVER="s3"
STORAGE_SIXMYK_KEY="votre_access_key"
STORAGE_SIXMYK_SECRET="votre_secret_key"
STORAGE_SIXMYK_BUCKET="sixmyk"
STORAGE_SIXMYK_REGION="us-east-1"
STORAGE_SIXMYK_ENDPOINT="http://seaweedfs:8336"
STORAGE_SIXMYK_FORCE_PATH_STYLE="true"
STORAGE_SIXMYK_PATH_STYLE="true"
STORAGE_SIXMYK_SIGNATURE_VERSION="v4"

# --- BUCKET PUBLIC (PUBLIC) ---
STORAGE_PUBLIC_DRIVER="s3"
STORAGE_PUBLIC_KEY="votre_access_key"
STORAGE_PUBLIC_SECRET="votre_secret_key"
STORAGE_PUBLIC_BUCKET="public"
STORAGE_PUBLIC_REGION="us-east-1"
STORAGE_PUBLIC_ENDPOINT="http://seaweedfs:8336"
STORAGE_PUBLIC_FORCE_PATH_STYLE="true"
STORAGE_PUBLIC_PATH_STYLE="true"
STORAGE_PUBLIC_SIGNATURE_VERSION="v4"

---

## 📁 3. Récupération de l'UUID du Dossier Cible

1. Connecte-toi à l'interface d'administration Directus.
2. Rends-toi dans la section Fichiers et crée ou ouvre le dossier à lier au bucket (ex: Public).
3. Copie l'UUID du dossier présent dans l'URL de ton navigateur :
   https://directus.votre-domaine.com/admin/files/folders/<DOSSIER_UUID>

---

## 🧩 4. Création de l'Extension Hook (storage-router)

Crée la structure de dossier suivante dans ton projet :

extensions/storage-router/
├── package.json
└── src/
    └── index.js

### A. Fichier extensions/storage-router/package.json

{
  "name": "directus-extension-storage-router",
  "version": "1.0.0",
  "type": "module",
  "directus:extension": {
    "type": "hook",
    "path": "dist/index.js",
    "source": "src/index.js",
    "host": "^10.0.0"
  },
  "scripts": {
    "build": "directus-extension build"
  },
  "dependencies": {
    "@aws-sdk/client-s3": "^3.0.0"
  },
  "devDependencies": {
    "@directus/extensions-sdk": "latest"
  }
}

### B. Fichier extensions/storage-router/src/index.js

Remplace la valeur de PUBLIC_FOLDER_ID par l'UUID récupéré à l'étape 3.

import { S3Client, CopyObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3';

export default ({ action }, { services, database, env }) => {
  const { FilesService } = services;

  // ID du dossier Directus lié au bucket public
  const PUBLIC_FOLDER_ID = 'b91fa07b-6e61-4c4f-abc9-25acea85d1eb';

  // Récupération des accès S3 depuis le .env
  const accessKeyId = env.STORAGE_SIXMYK_KEY || env.STORAGE_PUBLIC_KEY;
  const secretAccessKey = env.STORAGE_SIXMYK_SECRET || env.STORAGE_PUBLIC_SECRET;
  const endpoint = env.STORAGE_SIXMYK_ENDPOINT || 'http://seaweedfs:8336';
  const region = env.STORAGE_SIXMYK_REGION || 'us-east-1';

  const BUCKET_SOURCE = env.STORAGE_SIXMYK_BUCKET || 'sixmyk';
  const BUCKET_DEST = env.STORAGE_PUBLIC_BUCKET || 'public';

  action('files.upload', async (meta, context) => {
    const fileId = meta.key;

    try {
      const filesService = new FilesService({
        knex: database,
        schema: context.schema,
        accountability: context.accountability,
      });

      const file = await filesService.readOne(fileId);

      // Si le fichier est uploadé dans le dossier cible et n'est pas encore sur le bucket "public"
      if (file && file.folder === PUBLIC_FOLDER_ID && file.storage !== 'public') {
        const s3 = new S3Client({
          region,
          endpoint,
          credentials: {
            accessKeyId,
            secretAccessKey,
          },
          forcePathStyle: true,
        });

        const key = file.filename_disk;
        console.log(`[Storage Router] Déplacement S3 de ${key} : ${BUCKET_SOURCE} vers ${BUCKET_DEST}`);

        // 1. Copie physique entre buckets sur SeaweedFS/S3
        await s3.send(new CopyObjectCommand({
          Bucket: BUCKET_DEST,
          CopySource: `${BUCKET_SOURCE}/${key}`,
          Key: key,
        }));

        // 2. Suppression du fichier dans le bucket source (sixmyk)
        await s3.send(new DeleteObjectCommand({
          Bucket: BUCKET_SOURCE,
          Key: key,
        }));

        // 3. Mise à jour du stockage dans PostgreSQL
        await database('directus_files')
          .where({ id: fileId })
          .update({ storage: 'public' });

        console.log(`[Storage Router] Fichier ${fileId} déplacé avec succès sur le bucket S3 "${BUCKET_DEST}" !`);
      }
    } catch (error) {
      console.error('[Storage Router] Erreur lors du déplacement S3 :', error);
    }
  });
};

---

## 🛠️ 5. Compilation et Déploiement

1. Ouvre un terminal dans le dossier de l'extension :
   cd extensions/storage-router

2. Installe les dépendances et compile l'extension :
   npm install
   npm run build

3. Redémarre Directus pour charger le nouveau bundle JS :
   docker compose restart directus

---

## 🌐 6. Validation & Rendre les Fichiers Publics

### A. Activer les accès publics dans Directus
1. Dans l'administration Directus, va dans Paramètres ➔ Rôles & Permissions ➔ Public.
2. Dans la liste des collections, clique sur Directus Files (directus_files) et active l'autorisation de Lecture (Read).

### B. Tester le fonctionnement
1. Téléverse une image dans le dossier ciblé (Public).
2. Vérifie dans les logs Docker la présence de la ligne de succès.
3. Ouvre une fenêtre de navigation privée et teste l'URL de l'image :
   https://directus.votre-domaine.com/assets/<FILE_UUID>
4. Ou exécute une commande curl dans ton terminal :
   curl -I https://directus.votre-domaine.com/assets/<FILE_UUID>

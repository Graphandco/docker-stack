# Déploiements depuis le dashboard

## 1. Variables d'environnement (admin-api `.env`)

Ajoute dans `admin-api/.env` :

```bash
# Déploiements (deploy.sh des projets)
DEPLOY_RUNNER_IMAGE=deploy-runner
GH_TOKEN=ghp_xxx   # Token GitHub (Settings → Developer settings → Personal access tokens)
```

- **GH_TOKEN** : requis pour `gh workflow run` et `gh run watch`. Crée un token avec le scope `repo`.

## 2. Construire l'image deploy-runner

Depuis `~/www/docker-stack/apps/admin-dashboard` :

```bash
cd deploy-runner
docker build -t deploy-runner .
```

## 3. Projets configurés

Fichier `admin-api/deploy-projects.json` :

- **path** : chemin absolu du projet sur l'hôte (utilise le chemin réel, sans symlink)
- **repo** : `owner/repo` GitHub pour lister les derniers workflow runs (ex. `Graphandco/convert-image`)

```json
{
  "projects": [
    { "id": "convert-image", "label": "Convert Image", "path": "/var/www/docker-stack/apps/convert-image", "repo": "Graphandco/convert-image" },
    { "id": "o2-dentaire", "label": "O2 Dentaire", "path": "/var/www/docker-stack/nextapps/o2-dentaire", "repo": "Graphandco/o2-dentaire" }
  ]
}
```

## 4. Accès

Page : **Déploiements** dans la sidebar (ou `/deploy`).

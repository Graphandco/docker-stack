import {
   S3Client,
   CopyObjectCommand,
   DeleteObjectCommand,
} from "@aws-sdk/client-s3";

export default ({ action }, { services, database, env }) => {
   const { FilesService } = services;
   const PUBLIC_FOLDER_ID = "b91fa07b-6e61-4c4f-abc9-25acea85d1eb";

   // Lecture exacte depuis tes variables .env
   const accessKeyId = env.STORAGE_SIXMYK_KEY || env.STORAGE_PUBLIC_KEY;
   const secretAccessKey =
      env.STORAGE_SIXMYK_SECRET || env.STORAGE_PUBLIC_SECRET;
   const endpoint = env.STORAGE_SIXMYK_ENDPOINT || "http://seaweedfs:8336";
   const region = env.STORAGE_SIXMYK_REGION || "us-east-1";

   const BUCKET_SOURCE = env.STORAGE_SIXMYK_BUCKET || "sixmyk";
   const BUCKET_DEST = env.STORAGE_PUBLIC_BUCKET || "public";

   action("files.upload", async (meta, context) => {
      const fileId = meta.key;

      try {
         const filesService = new FilesService({
            knex: database,
            schema: context.schema,
            accountability: context.accountability,
         });

         const file = await filesService.readOne(fileId);

         // Si le fichier est téléversé dans le dossier "public" mais est stocké sur "sixmyk"
         if (
            file &&
            file.folder === PUBLIC_FOLDER_ID &&
            file.storage !== "public"
         ) {
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
            console.log(
               `[Storage Router] Déplacement S3 de ${key} : ${BUCKET_SOURCE} ➔ ${BUCKET_DEST}`,
            );

            // 1. Copie du fichier physique du bucket sixmyk vers public
            await s3.send(
               new CopyObjectCommand({
                  Bucket: BUCKET_DEST,
                  CopySource: `${BUCKET_SOURCE}/${key}`,
                  Key: key,
               }),
            );

            // 2. Suppression du fichier source dans sixmyk
            await s3.send(
               new DeleteObjectCommand({
                  Bucket: BUCKET_SOURCE,
                  Key: key,
               }),
            );

            // 3. Mise à jour de la BDD pour pointer sur public
            await database("directus_files")
               .where({ id: fileId })
               .update({ storage: "public" });

            console.log(
               `[Storage Router] ✅ Fichier ${fileId} déplacé avec succès sur le bucket S3 "${BUCKET_DEST}" !`,
            );
         }
      } catch (error) {
         console.error(
            "[Storage Router] ❌ Erreur lors du déplacement S3 :",
            error,
         );
      }
   });
};

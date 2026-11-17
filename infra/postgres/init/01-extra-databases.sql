-- Exécuté une seule fois à l'init (pgdata vide). Ajoutez d'autres CREATE DATABASE ici si besoin.
-- La base ${POSTGRES_DB} (ex. payload) est déjà créée par l'image.

CREATE DATABASE test
  WITH ENCODING 'UTF8' LC_COLLATE = 'C' LC_CTYPE = 'C' TEMPLATE template0;

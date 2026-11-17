# Image avec WordPress complet + WP-CLI (wordpress:cli ne contient pas le coeur WP)
FROM wordpress:7.1-php8.3-apache

# Installation de WP-CLI (méthode officielle)
RUN curl -sS -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x /usr/local/bin/wp

# WP-CLI en mode non-interactif (pour les scripts/API)
ENV WP_CLI_CACHE_DIR=/tmp/wp-cli-cache

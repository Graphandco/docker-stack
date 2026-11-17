<?php
// define('WP_DEBUG', true);
// define('WP_DEBUG_DISPLAY', true); // Affiche les erreurs à l'écran
// define('WP_DEBUG_LOG', true);     // Sauvegarde les erreurs dans wp-content/debug.log
// define('SCRIPT_DEBUG', true);     // Utilise les versions non minifiées des scripts CSS/JS
// @ini_set('display_errors', 1);    // Active l'affichage des erreurs PHP

// SeaweedFS / S3 Uploads.
// Le plugin reste activé uniquement sur le sous-site pilote.
define('S3_UPLOADS_BUCKET', getenv('WORDPRESS_S3_BUCKET'));
define('S3_UPLOADS_KEY', getenv('WORDPRESS_S3_ACCESS_KEY'));
define('S3_UPLOADS_SECRET', getenv('WORDPRESS_S3_SECRET_KEY'));
define('S3_UPLOADS_REGION', getenv('WORDPRESS_S3_REGION'));
define('S3_UPLOADS_OBJECT_ACL', 'private');
$s3_uploads_public_host = isset($_SERVER['HTTP_HOST'])
    ? preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'])
    : 'sites.graphandco.net';

define(
    'S3_UPLOADS_BUCKET_URL',
    'https://' . $s3_uploads_public_host . '/wp-content'
);

// Redis
define('WP_REDIS_HOST', 'redis-cache');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
define('WP_REDIS_DATABASE', 0);
define('WP_CACHE', true);
define('WP_CACHE_KEY_SALT', 'wp_multisite:');
define('WP_REDIS_MAXTTL', 86400);

// Cookie domain
// define( 'COOKIE_DOMAIN', '.infirmiere68000.fr' ); 

// Base de données Docker - Lecture depuis les variables d'environnement
define('DB_NAME', getenv('WORDPRESS_DB_NAME'));
define('DB_USER', getenv('WORDPRESS_DB_USER'));
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
define('DB_HOST', getenv('WORDPRESS_DB_HOST'));
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Variable pour détecter Redis
// define('WP_REDIS_HOST', 'redis');

// Multisite & cache (identiques à l’ancienne install)
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'sites.graphandco.net');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

// Derrière reverse proxy (Caddy)
if (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
) {
    $_SERVER['HTTPS'] = 'on';
}

// Force les URLs principales en HTTPS pour le site courant
// if (strpos($_SERVER['HTTP_HOST'], 'sites.graphandco.net') !== false) {
//     define('WP_HOME', 'https://sites.graphandco.net');
//     define('WP_SITEURL', 'https://sites.graphandco.net');
// }

$table_prefix = 'wp_';

if ( !defined('ABSPATH') )
    define('ABSPATH', __DIR__ . '/');
require_once ABSPATH . 'wp-settings.php';

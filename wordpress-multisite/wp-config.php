<?php
// define('WP_DEBUG', true);
// define('WP_DEBUG_DISPLAY', true); // Affiche les erreurs à l'écran
// define('WP_DEBUG_LOG', true);     // Sauvegarde les erreurs dans wp-content/debug.log
// define('SCRIPT_DEBUG', true);     // Utilise les versions non minifiées des scripts CSS/JS
// @ini_set('display_errors', 1);    // Active l'affichage des erreurs PHP


// Base de données Docker - Lecture depuis les variables d'environnement
define('DB_NAME', getenv('WORDPRESS_DB_NAME'));
define('DB_USER', getenv('WORDPRESS_DB_USER'));
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
define('DB_HOST', getenv('WORDPRESS_DB_HOST'));
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

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
if (strpos($_SERVER['HTTP_HOST'], 'sites.graphandco.net') !== false) {
    define('WP_HOME', 'https://sites.graphandco.net');
    define('WP_SITEURL', 'https://sites.graphandco.net');
}

$table_prefix = 'wp_';

if ( !defined('ABSPATH') )
    define('ABSPATH', __DIR__ . '/');
require_once ABSPATH . 'wp-settings.php';

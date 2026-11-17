<?php
/**
 * Configure S3 Uploads for the SeaweedFS S3-compatible endpoint.
 */

$autoload = WP_CONTENT_DIR . '/plugins/s3-uploads/vendor/autoload.php';

if (is_readable($autoload)) {
    require_once $autoload;
}

add_filter('s3_uploads_s3_client_params', static function (array $params): array {
    $params['endpoint'] = getenv('WORDPRESS_S3_ENDPOINT');
    $params['use_path_style_endpoint'] = true;

    return $params;
});

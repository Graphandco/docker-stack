<?php
add_action('delete_attachment', function (int $post_id): void {
    if (!class_exists('\S3_Uploads\Plugin')) return;

    $plugin = \S3_Uploads\Plugin::get_instance();
    $client = $plugin->s3();
    $bucket = $plugin->get_s3_bucket();
    $file   = get_attached_file($post_id);
    if (!$file || !str_starts_with($file, 's3://')) return;

    $key   = preg_replace('#^s3://[^/]+/#', '', $file);
    $dir   = dirname($key);
    $base  = pathinfo($key, PATHINFO_FILENAME);
    $regex = '/^' . preg_quote($base, '/') . '(-\d+x\d+)?(-scaled|-rotated)?(\.[a-z0-9]+)(\.webp)?$/i';

    $keys = [];
    foreach ($client->getPaginator('ListObjectsV2', ['Bucket' => $bucket, 'Prefix' => $dir . '/' . $base]) as $page) {
        foreach ($page['Contents'] ?? [] as $obj) {
            if (preg_match($regex, basename($obj['Key']))) $keys[] = ['Key' => $obj['Key']];
        }
    }

    foreach (array_chunk($keys, 1000) as $chunk) {
        $client->deleteObjects(['Bucket' => $bucket, 'Delete' => ['Objects' => $chunk, 'Quiet' => true]]);
    }
}, 30);

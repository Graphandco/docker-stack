<?php
/**
 * Plugin Name: Tailles intermédiaires 2x désactivées
 * Description: Empêche la génération des tailles 1536x1536 et 2048x2048 (cœur WP) sur tous les sites du réseau.
 */

add_filter(
	'intermediate_image_sizes_advanced',
	function ( array $sizes ): array {
		unset( $sizes['1536x1536'], $sizes['2048x2048'] );
		return $sizes;
	}
);

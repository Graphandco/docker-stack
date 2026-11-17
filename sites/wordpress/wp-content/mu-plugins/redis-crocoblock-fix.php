<?php
/*
  Plugin Name: Fix Crocoblock Redis Flood
  Description: Bascule le groupe 'default' en non-persistant pour stopper le spam de cherry_x_modules.
*/

if ( function_exists( 'wp_cache_add_non_persistent_groups' ) ) {
    wp_cache_add_non_persistent_groups( array( 'default' ) );
}

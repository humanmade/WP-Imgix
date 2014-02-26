<?php

/**
 * Plugin Name: IMGIX
 * Author: Human Made Limited
 */

if ( ! defined( 'WP_IMGIX_KEY' ) ) {
	return;
}

require_once( dirname( __FILE__ ) . '/inc/class-wp-imgix.php' );

add_action( 'plugins_loaded', function() {
	$imgix = WP_Imgix::get_instance();
	add_filter( 'image_downsize', array( $imgix, 'filter_image_downsize'), 10, 3 );
	add_filter( 'intermediate_image_sizes_advanced', array( $imgix, 'wp_intermediate_sites' ) );
});

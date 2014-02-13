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
	$imgix = new WP_Imgix( WP_IMGIX_KEY );
	add_filter( 'image_downsize', array( $imgix, 'filter_image_downsize'), 10, 3 );

	/**
	 * Disable WordPress generating images for all the additional sizes, as we don't need that thang no more!
	 */
	add_filter( 'intermediate_image_sizes_advanced', function() {
		return array();
	});
});

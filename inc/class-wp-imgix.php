<?php

class WP_Imgix {

	private $key = '';
	private $domain = '';

	protected static $instance;

	public function __construct( $domain = WP_IMGIX_DOMAIN, $key = WP_IMGIX_KEY ) {
		$this->key = $key;
		$this->domain = $domain;
	}

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Disable WordPress generating images for all the additional sizes, as we don't need that thang no more!
	 */
	public function wp_intermediate_sizes() {
		return array();
	}

	public function filter_image_downsize( $false, $attachment_id, $size ) {

		$meta = wp_get_attachment_metadata( $attachment_id );
		$size = $this->parse_size( $size );
		$url  = $this->get_thumbnail_url( wp_get_attachment_url( $attachment_id ), $size );

		$new_size = $size['crop'] == false ? wp_constrain_dimensions( $meta['width'], $meta['height'], $size['width'], $size['height'] ) : array( $size['width'], $size['height'] );

		return array(
			$url,
			$new_size[0],
			$new_size[1],
			true
		);
	}

	/**
	 * Get a thumbnail URL for a given image
	 *
	 * @param string $src Image URL
	 * @param array $size {
	 *     @type string $width
	 *     @type string $height
	 * }
	 * @return string
	 */
	public function get_thumbnail_url( $src, $size ) {

		$src = parse_url( $src );

		$url = '//' . $this->domain . $src['path'];

		if ( ! empty( $size['width'] ) ) {
			$url = add_query_arg( 'w', $size['width'], $url );
		}

		if ( ! empty( $size['height'] ) ) {
			$url = add_query_arg( 'h', $size['height'], $url );
		}

		if ( ! empty( $size['width'] ) || ! empty( $size['height'] ) ) {
			$url = add_query_arg( 'fit', $size['crop'] ? 'crop' : 'max', $url );
		}

		return $url;
	}

	/**
	 * Parse size params
	 *
	 * @param $size
	 * @return array
	 */
	private function parse_size( $size ) {

		global $_wp_additional_image_sizes;
		$new_size = array( 'width' => 0, 'height' => 0, 'crop' => false );

		if ( is_string( $size ) && strpos( $size, '=' ) ) {
			$size = wp_parse_args( $size );
		}

		if ( isset( $size['width'] ) ) {
			$new_size['width'] = $size['width'];
		}

		if ( isset( $size['height'] ) ) {
			$new_size['height'] = $size['height'];
		}

		if ( isset( $size['crop'] ) ) {
			$new_size['crop'] = $size['crop'];
		}

		if ( is_string( $size ) ) {

			if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$new_size['width']  = intval( $_wp_additional_image_sizes[ $size ]['width'] );
				$new_size['height'] = intval( $_wp_additional_image_sizes[ $size ]['height'] );
				$new_size['crop']   = (bool) $_wp_additional_image_sizes[ $size ]['crop'];
			} else {
				$new_size['height'] = get_option( "{$size}_size_h" );
				$new_size['width']  = get_option( "{$size}_size_w" );
				$new_size['crop']   = get_option( "{$size}_crop" );
			}
		}

		if ( is_array( $size ) && isset( $size[0] ) ) {

			$new_size['width'] = $size[0];

			if ( isset( $size[1] ) ) {
				$new_size['height'] = $size[1];
			}

			if ( isset( $size[2] ) ) {
				$new_size['crop'] = $size[2];
			}
		}

		return $new_size;
	}
}
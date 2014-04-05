<?php

class WP_Imgix {

	private $key = '';
	private $url_base = 'https://i.embed.ly/1/display/';

	protected static $instance;

	public function __construct( $key = WP_IMGIX_KEY ) {
		$this->key = $key;
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

		$size = $this->parse_size( $size );
		$url = $this->get_thumbnail_url( wp_get_attachment_url( $attachment_id ), $size );

		return array(
			$url,
			$size['crop'] ? $size['width'] : 0,
			$size['crop'] ? $size['height'] : 0
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

		$url = $this->url_base;

		if ( ! empty( $size['crop'] ) ) {
			$url .= 'crop/';
		} else {
			$url .= 'resize/';
		}

		$url = add_query_arg( array(
			'url' => urlencode( $src ),
			'width' => ! empty( $size['width'] ) ? $size['width'] : '',
			'height' => ! empty( $size['height'] ) ? $size['height'] : '',
			'key' => $this->key
		), $url );

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
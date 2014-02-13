<?php

class WP_Imgix {

	private $key = '';
	private $url_base = 'https://i.embed.ly/1/display/';

	public function __construct( $key ) {
		$this->key = $key;
	}

	public function filter_image_downsize( $false, $attachment_id, $size ) {

		$size = $this->parse_size( $size );
		$url = $this->build_image_url( wp_get_attachment_url( $attachment_id ), $size );

		return array( $url, 0, 0 );
	}

	/**
	 * Build an image url for the resizes image
	 *
	 * @param string $src
	 * @param array $size
	 * @return string
	 */
	private function build_image_url( $src, $size ) {

		$url = $this->url_base;

		if ( ! empty( $size['crop'] ) ) {
			$url .= 'crop/';
		} else {
			$url .= 'resize/';
		}

		$url = add_query_arg( array(
			'url' => urlencode( $src ),
			'width' => ! empty( $size['width'] ) ? $size['width'] : '',
			'height' => ! empty( $size['width'] ) ? $size['height'] : '',
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


		if ( is_string( $size ) && strpos( $size, '=' ) ) {
			$size = wp_parse_args( $size );
		}

		if ( is_string( $size ) && isset( $_wp_additional_image_sizes[$size] ) ) {
			$size = $_wp_additional_image_sizes[$size];
		}

		if ( is_array( $size ) && isset( $size[0] ) ) {

			$size['width'] = $size[0];
			unset( $size[0] );

			if ( isset( $size[1] ) ) {
				$size['height'] = $size[1];
				unset( $size[1] );
			}

			if ( isset( $size[2] ) ) {
				$size['crop'] = $size[2];
				unset( $size[2] );
			}
		}

		return $size;
	}
}
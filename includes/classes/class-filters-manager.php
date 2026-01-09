<?php
/**
 * Instagram-Style Filters Manager
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages Instagram-style image filters
 */
class Filters_Manager extends Image_Processor {

	/**
	 * Available filters
	 *
	 * @var array
	 */
	private static $filters = array(
		'vintage'       => array(
			'name'        => 'Vintage',
			'description' => 'Warm sepia tones with reduced contrast',
		),
		'warm'          => array(
			'name'        => 'Warm',
			'description' => 'Boost warm tones, reduce blues',
		),
		'cool'          => array(
			'name'        => 'Cool',
			'description' => 'Enhance cool blues and reduce warm tones',
		),
		'high_contrast' => array(
			'name'        => 'High Contrast',
			'description' => 'Increase contrast for dramatic effect',
		),
		'grayscale'     => array(
			'name'        => 'Grayscale',
			'description' => 'Classic black and white',
		),
	);

	/**
	 * Get available filters
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		return self::$filters;
	}

	/**
	 * Apply filter to image
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $filter_name Filter name.
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function apply_filter( int $attachment_id, string $filter_name ) {
		if ( ! array_key_exists( $filter_name, self::$filters ) ) {
			return false;
		}

		$image = self::get_image_editor_instance( $attachment_id );
		if ( ! $image || is_wp_error( $image ) ) {
			return false;
		}

		// Apply the filter
		switch ( $filter_name ) {
			case 'vintage':
				self::apply_vintage_filter( $image );
				break;
			case 'warm':
				self::apply_warm_filter( $image );
				break;
			case 'cool':
				self::apply_cool_filter( $image );
				break;
			case 'high_contrast':
				self::apply_high_contrast_filter( $image );
				break;
			case 'grayscale':
				$image->filter( 'grayscale' );
				break;
		}

		return self::save_image( $image, $attachment_id, 'filter-' . $filter_name );
	}

	/**
	 * Apply vintage filter
	 *
	 * @param \WP_Image_Editor $image Image editor instance.
	 */
	private static function apply_vintage_filter( $image ): void {
		$image->filter( 'brightness', -10 );
		$image->filter( 'contrast', -15 );
	}

	/**
	 * Apply warm filter
	 *
	 * @param \WP_Image_Editor $image Image editor instance.
	 */
	private static function apply_warm_filter( $image ): void {
		$image->filter( 'brightness', 5 );
	}

	/**
	 * Apply cool filter
	 *
	 * @param \WP_Image_Editor $image Image editor instance.
	 */
	private static function apply_cool_filter( $image ): void {
		$image->filter( 'brightness', -5 );
	}

	/**
	 * Apply high contrast filter
	 *
	 * @param \WP_Image_Editor $image Image editor instance.
	 */
	private static function apply_high_contrast_filter( $image ): void {
		$image->filter( 'contrast', 30 );
	}
}

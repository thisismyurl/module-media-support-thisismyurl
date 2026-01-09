<?php
/**
 * Social Preview Simulator
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
 * Simulates how images will appear on social platforms
 */
class Preview_Simulator {

	/**
	 * Social platform specifications
	 *
	 * @var array
	 */
	private static $platforms = array(
		'instagram' => array(
			'name'          => 'Instagram',
			'square'        => array( 'width' => 1080, 'height' => 1080 ),
			'portrait'      => array( 'width' => 1080, 'height' => 1350 ),
			'story'         => array( 'width' => 1080, 'height' => 1920 ),
			'profile_size'  => 150,
		),
		'facebook'  => array(
			'name'          => 'Facebook',
			'post'          => array( 'width' => 1200, 'height' => 630 ),
			'cover'         => array( 'width' => 820, 'height' => 312 ),
			'profile_size'  => 170,
		),
		'twitter'   => array(
			'name'          => 'Twitter/X',
			'post'          => array( 'width' => 1200, 'height' => 675 ),
			'header'        => array( 'width' => 1500, 'height' => 500 ),
			'profile_size'  => 400,
		),
		'linkedin'  => array(
			'name'          => 'LinkedIn',
			'post'          => array( 'width' => 1200, 'height' => 627 ),
			'banner'        => array( 'width' => 1584, 'height' => 396 ),
			'profile_size'  => 400,
		),
		'pinterest' => array(
			'name'          => 'Pinterest',
			'pin'           => array( 'width' => 1000, 'height' => 1500 ),
			'profile_size'  => 165,
		),
		'tiktok'    => array(
			'name'          => 'TikTok',
			'video'         => array( 'width' => 1080, 'height' => 1920 ),
			'profile_size'  => 200,
		),
	);

	/**
	 * Get platform specifications
	 *
	 * @return array
	 */
	public static function get_platforms(): array {
		return self::$platforms;
	}

	/**
	 * Generate preview data for a platform
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $platform Platform name.
	 * @param string $type Preview type (post, story, etc.).
	 * @return array|false Preview data or false on failure.
	 */
	public static function generate_preview( int $attachment_id, string $platform, string $type = 'post' ) {
		if ( ! array_key_exists( $platform, self::$platforms ) ) {
			return false;
		}

		$platform_spec = self::$platforms[ $platform ];
		
		if ( ! array_key_exists( $type, $platform_spec ) || ! is_array( $platform_spec[ $type ] ) ) {
			// Try to find first available format
			foreach ( $platform_spec as $key => $value ) {
				if ( is_array( $value ) && isset( $value['width'] ) && isset( $value['height'] ) ) {
					$type = $key;
					break;
				}
			}
		}

		$spec = $platform_spec[ $type ] ?? false;
		if ( ! $spec || ! isset( $spec['width'] ) || ! isset( $spec['height'] ) ) {
			return false;
		}

		$image = Image_Processor::get_image_editor_instance( $attachment_id );
		if ( ! $image || is_wp_error( $image ) ) {
			return false;
		}

		// Get current dimensions
		$size = $image->get_size();

		// Calculate how the image fits
		$fit_data = self::calculate_fit( $size['width'], $size['height'], $spec['width'], $spec['height'] );

		return array(
			'platform'       => $platform,
			'platform_name'  => $platform_spec['name'],
			'type'           => $type,
			'target_width'   => $spec['width'],
			'target_height'  => $spec['height'],
			'current_width'  => $size['width'],
			'current_height' => $size['height'],
			'fit'            => $fit_data['fit'],
			'crop_required'  => $fit_data['crop_required'],
			'display_width'  => $fit_data['display_width'],
			'display_height' => $fit_data['display_height'],
		);
	}

	/**
	 * Calculate how image fits in target dimensions
	 *
	 * @param int $img_width Image width.
	 * @param int $img_height Image height.
	 * @param int $target_width Target width.
	 * @param int $target_height Target height.
	 * @return array Fit data.
	 */
	private static function calculate_fit( int $img_width, int $img_height, int $target_width, int $target_height ): array {
		$img_ratio    = $img_width / $img_height;
		$target_ratio = $target_width / $target_height;

		$crop_required = false;
		$fit           = 'exact';

		if ( abs( $img_ratio - $target_ratio ) > 0.01 ) {
			$crop_required = true;
			if ( $img_ratio > $target_ratio ) {
				$fit = 'horizontal-crop';
			} else {
				$fit = 'vertical-crop';
			}
		}

		// Calculate display dimensions (fit within target)
		if ( $img_ratio > $target_ratio ) {
			$display_width  = $target_width;
			$display_height = (int) ( $target_width / $img_ratio );
		} else {
			$display_height = $target_height;
			$display_width  = (int) ( $target_height * $img_ratio );
		}

		return array(
			'fit'            => $fit,
			'crop_required'  => $crop_required,
			'display_width'  => $display_width,
			'display_height' => $display_height,
		);
	}

	/**
	 * Generate previews for all platforms
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Array of preview data for each platform.
	 */
	public static function generate_all_previews( int $attachment_id ): array {
		$previews = array();

		foreach ( array_keys( self::$platforms ) as $platform ) {
			$preview = self::generate_preview( $attachment_id, $platform );
			if ( $preview ) {
				$previews[ $platform ] = $preview;
			}
		}

		return $previews;
	}
}

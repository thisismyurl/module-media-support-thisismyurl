<?php
/**
 * Social Media Crop Presets Manager
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
 * Manages social media crop presets
 */
class Crop_Manager extends Image_Processor {

	/**
	 * Social platform crop presets
	 *
	 * @var array
	 */
	private static $presets = array(
		'instagram_square'   => array(
			'name'   => 'Instagram Square',
			'width'  => 1080,
			'height' => 1080,
			'ratio'  => '1:1',
		),
		'instagram_portrait' => array(
			'name'   => 'Instagram Portrait',
			'width'  => 1080,
			'height' => 1350,
			'ratio'  => '4:5',
		),
		'instagram_story'    => array(
			'name'   => 'Instagram Story',
			'width'  => 1080,
			'height' => 1920,
			'ratio'  => '9:16',
		),
		'facebook_post'      => array(
			'name'   => 'Facebook Post',
			'width'  => 1200,
			'height' => 630,
			'ratio'  => '1.91:1',
		),
		'facebook_cover'     => array(
			'name'   => 'Facebook Cover',
			'width'  => 820,
			'height' => 312,
			'ratio'  => '2.63:1',
		),
		'twitter_post'       => array(
			'name'   => 'Twitter/X Post',
			'width'  => 1200,
			'height' => 675,
			'ratio'  => '16:9',
		),
		'linkedin_post'      => array(
			'name'   => 'LinkedIn Post',
			'width'  => 1200,
			'height' => 627,
			'ratio'  => '1.91:1',
		),
		'pinterest_pin'      => array(
			'name'   => 'Pinterest Pin',
			'width'  => 1000,
			'height' => 1500,
			'ratio'  => '2:3',
		),
		'tiktok'             => array(
			'name'   => 'TikTok',
			'width'  => 1080,
			'height' => 1920,
			'ratio'  => '9:16',
		),
		'youtube_thumbnail'  => array(
			'name'   => 'YouTube Thumbnail',
			'width'  => 1280,
			'height' => 720,
			'ratio'  => '16:9',
		),
	);

	/**
	 * Get available crop presets
	 *
	 * @return array
	 */
	public static function get_presets(): array {
		return self::$presets;
	}

	/**
	 * Crop image to social media preset
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $preset_name Preset name.
	 * @param bool   $center Whether to center crop (default true).
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function crop_to_preset( int $attachment_id, string $preset_name, bool $center = true ) {
		if ( ! array_key_exists( $preset_name, self::$presets ) ) {
			return false;
		}

		$preset = self::$presets[ $preset_name ];
		$image  = self::get_image_editor_instance( $attachment_id );
		
		if ( ! $image || is_wp_error( $image ) ) {
			return false;
		}

		$size     = $image->get_size();
		$orig_w   = $size['width'];
		$orig_h   = $size['height'];
		$target_w = $preset['width'];
		$target_h = $preset['height'];

		// Calculate crop dimensions to maintain aspect ratio
		$target_ratio = $target_w / $target_h;
		$orig_ratio   = $orig_w / $orig_h;

		// Check for exact match first to avoid unnecessary calculations
		if ( abs( $orig_ratio - $target_ratio ) < 0.001 ) {
			// Aspect ratios match - just resize
			$image->resize( $target_w, $target_h, false );
		} elseif ( $orig_ratio > $target_ratio ) {
			// Original is wider - crop width
			$crop_w = (int) ( $orig_h * $target_ratio );
			$crop_h = $orig_h;
			$x      = $center ? (int) ( ( $orig_w - $crop_w ) / 2 ) : 0;
			$y      = 0;
			$image->crop( $x, $y, $crop_w, $crop_h, $target_w, $target_h );
		} else {
			// Original is taller - crop height
			$crop_w = $orig_w;
			$crop_h = (int) ( $orig_w / $target_ratio );
			$x      = 0;
			$y      = $center ? (int) ( ( $orig_h - $crop_h ) / 2 ) : 0;
			$image->crop( $x, $y, $crop_w, $crop_h, $target_w, $target_h );
		}

		return self::save_image( $image, $attachment_id, 'crop-' . $preset_name );
	}
}

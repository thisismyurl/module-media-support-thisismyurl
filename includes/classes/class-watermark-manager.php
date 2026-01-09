<?php
/**
 * Watermark Manager
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
 * Manages watermark and logo placement
 */
class Watermark_Manager extends Image_Processor {

	/**
	 * Position presets for watermarks
	 *
	 * @var array
	 */
	private static $positions = array(
		'top-left'     => array( 'x' => 'left', 'y' => 'top' ),
		'top-right'    => array( 'x' => 'right', 'y' => 'top' ),
		'bottom-left'  => array( 'x' => 'left', 'y' => 'bottom' ),
		'bottom-right' => array( 'x' => 'right', 'y' => 'bottom' ),
		'center'       => array( 'x' => 'center', 'y' => 'center' ),
	);

	/**
	 * Get available positions
	 *
	 * @return array
	 */
	public static function get_positions(): array {
		return self::$positions;
	}

	/**
	 * Add watermark to image
	 *
	 * @param int    $attachment_id Image attachment ID.
	 * @param int    $watermark_id Watermark attachment ID.
	 * @param string $position Position preset.
	 * @param int    $opacity Opacity (0-100).
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function add_watermark( int $attachment_id, int $watermark_id, string $position = 'bottom-right', int $opacity = 80 ) {
		$image = self::get_image_editor_instance( $attachment_id );
		$watermark_path = get_attached_file( $watermark_id );

		if ( ! $image || is_wp_error( $image ) || ! $watermark_path ) {
			return false;
		}

		if ( ! array_key_exists( $position, self::$positions ) ) {
			$position = 'bottom-right';
		}

		$pos_data = self::$positions[ $position ];
		$size     = $image->get_size();
		$padding  = 20;

		// Calculate watermark position
		$x = $y = 0;
		$watermark_info = getimagesize( $watermark_path );
		if ( ! $watermark_info ) {
			return false;
		}

		$wm_width  = $watermark_info[0];
		$wm_height = $watermark_info[1];

		// Calculate X position
		if ( $pos_data['x'] === 'left' ) {
			$x = $padding;
		} elseif ( $pos_data['x'] === 'right' ) {
			$x = $size['width'] - $wm_width - $padding;
		} else {
			$x = (int) ( ( $size['width'] - $wm_width ) / 2 );
		}

		// Calculate Y position
		if ( $pos_data['y'] === 'top' ) {
			$y = $padding;
		} elseif ( $pos_data['y'] === 'bottom' ) {
			$y = $size['height'] - $wm_height - $padding;
		} else {
			$y = (int) ( ( $size['height'] - $wm_height ) / 2 );
		}

		// Apply watermark - WordPress doesn't have native watermark support in WP_Image_Editor
		// So we'll use GD/Imagick directly
		$file_path = get_attached_file( $attachment_id );
		
		$editor_type = self::get_image_editor();
		if ( $editor_type === 'imagick' ) {
			$result = self::apply_watermark_imagick( $file_path, $watermark_path, $x, $y, $opacity );
		} else {
			$result = self::apply_watermark_gd( $file_path, $watermark_path, $x, $y, $opacity );
		}

		if ( ! $result ) {
			return false;
		}

		$upload_dir   = wp_upload_dir();
		$pathinfo     = pathinfo( $file_path );
		$new_filename = $pathinfo['filename'] . '-watermark.' . $pathinfo['extension'];
		$new_filepath = $pathinfo['dirname'] . '/' . $new_filename;

		// Move temp file instead of copy for reliability
		if ( ! rename( $result, $new_filepath ) ) {
			// Fallback to copy if rename fails (e.g., across filesystems)
			if ( ! copy( $result, $new_filepath ) ) {
				return false;
			}
			@unlink( $result );
		}

		return array(
			'path' => $new_filepath,
			'url'  => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $new_filepath ),
		);
	}

	/**
	 * Apply watermark using Imagick
	 *
	 * @param string $image_path Image path.
	 * @param string $watermark_path Watermark path.
	 * @param int    $x X position.
	 * @param int    $y Y position.
	 * @param int    $opacity Opacity.
	 * @return string|false Temp file path or false.
	 */
	private static function apply_watermark_imagick( string $image_path, string $watermark_path, int $x, int $y, int $opacity ) {
		if ( ! class_exists( 'Imagick' ) ) {
			return false;
		}

		$image     = new \Imagick( $image_path );
		$watermark = new \Imagick( $watermark_path );

		// Set opacity
		$watermark->setImageOpacity( $opacity / 100 );

		// Composite watermark onto image
		$image->compositeImage( $watermark, \Imagick::COMPOSITE_OVER, $x, $y );

		// Save to temp file
		$temp_file = wp_tempnam( basename( $image_path ) );
		$image->writeImage( $temp_file );

		$image->clear();
		$image->destroy();
		$watermark->clear();
		$watermark->destroy();

		return $temp_file;
	}

	/**
	 * Apply watermark using GD
	 *
	 * @param string $image_path Image path.
	 * @param string $watermark_path Watermark path.
	 * @param int    $x X position.
	 * @param int    $y Y position.
	 * @param int    $opacity Opacity.
	 * @return string|false Temp file path or false.
	 */
	private static function apply_watermark_gd( string $image_path, string $watermark_path, int $x, int $y, int $opacity ) {
		$image_info = getimagesize( $image_path );
		$wm_info    = getimagesize( $watermark_path );

		if ( ! $image_info || ! $wm_info ) {
			return false;
		}

		// Create image resources
		$image = self::create_image_from_file( $image_path, $image_info['mime'] );
		$watermark = self::create_image_from_file( $watermark_path, $wm_info['mime'] );

		if ( ! $image || ! $watermark ) {
			return false;
		}

		// Copy watermark onto image with alpha
		imagecopymerge( $image, $watermark, $x, $y, 0, 0, imagesx( $watermark ), imagesy( $watermark ), $opacity );

		// Save to temp file
		$temp_file = wp_tempnam( basename( $image_path ) );
		
		switch ( $image_info['mime'] ) {
			case 'image/jpeg':
				imagejpeg( $image, $temp_file, 90 );
				break;
			case 'image/png':
				imagepng( $image, $temp_file );
				break;
			case 'image/gif':
				imagegif( $image, $temp_file );
				break;
			default:
				return false;
		}

		imagedestroy( $image );
		imagedestroy( $watermark );

		return $temp_file;
	}

	/**
	 * Create GD image resource from file
	 *
	 * @param string $path File path.
	 * @param string $mime MIME type.
	 * @return resource|false
	 */
	private static function create_image_from_file( string $path, string $mime ) {
		switch ( $mime ) {
			case 'image/jpeg':
				return imagecreatefromjpeg( $path );
			case 'image/png':
				return imagecreatefrompng( $path );
			case 'image/gif':
				return imagecreatefromgif( $path );
			default:
				return false;
		}
	}
}

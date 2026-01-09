<?php
/**
 * Base Image Processor Class
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
 * Base class for image processing operations
 */
class Image_Processor {

	/**
	 * Check if GD or Imagick is available
	 *
	 * @return string 'gd', 'imagick', or 'none'
	 */
	public static function get_image_editor(): string {
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			return 'imagick';
		} elseif ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
			return 'gd';
		}
		return 'none';
	}

	/**
	 * Get image resource from attachment ID
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return \WP_Image_Editor|false
	 */
	public static function get_image_editor_instance( int $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path ) {
			return false;
		}

		return wp_get_image_editor( $file_path );
	}

	/**
	 * Save processed image
	 *
	 * @param \WP_Image_Editor $image Image editor instance.
	 * @param int              $attachment_id Attachment ID.
	 * @param string           $suffix Filename suffix.
	 * @return array|false Array with file path and URL, or false on failure.
	 */
	public static function save_image( $image, int $attachment_id, string $suffix ) {
		if ( is_wp_error( $image ) ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$original_file = get_attached_file( $attachment_id );
		$pathinfo = pathinfo( $original_file );
		
		$new_filename = $pathinfo['filename'] . '-' . $suffix . '.' . $pathinfo['extension'];
		$new_filepath = $pathinfo['dirname'] . '/' . $new_filename;

		$saved = $image->save( $new_filepath );
		
		if ( is_wp_error( $saved ) ) {
			return false;
		}

		return array(
			'path' => $saved['path'],
			'url'  => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $saved['path'] ),
		);
	}
}

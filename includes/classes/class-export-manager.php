<?php
/**
 * Export Manager
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
 * Manages optimized image exports for social platforms
 */
class Export_Manager extends Image_Processor {

	/**
	 * Export image in multiple social formats
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $platforms Array of platform names to export for.
	 * @return array Results with URLs and paths for each platform.
	 */
	public static function export_multi_platform( int $attachment_id, array $platforms ): array {
		$results = array();
		$presets = Crop_Manager::get_presets();

		foreach ( $platforms as $platform ) {
			// Find matching preset
			foreach ( $presets as $preset_name => $preset_data ) {
				if ( strpos( $preset_name, strtolower( $platform ) ) !== false ) {
					$result = Crop_Manager::crop_to_preset( $attachment_id, $preset_name );
					if ( $result ) {
						$results[ $preset_name ] = $result;
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Create a zip file with all exported versions
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $platforms Platforms to export.
	 * @return string|false Path to zip file or false on failure.
	 */
	public static function export_as_bundle( int $attachment_id, array $platforms ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			// Log error for debugging
			error_log( 'TIMU Media: ZipArchive class not available for bundle export' );
			return false;
		}

		$exports = self::export_multi_platform( $attachment_id, $platforms );
		
		if ( empty( $exports ) ) {
			return false;
		}

		$post      = get_post( $attachment_id );
		$zip_name  = sanitize_file_name( $post->post_title ) . '-social-bundle.zip';
		$upload_dir = wp_upload_dir();
		$zip_path  = $upload_dir['path'] . '/' . $zip_name;

		$zip = new \ZipArchive();
		if ( $zip->open( $zip_path, \ZipArchive::CREATE ) !== true ) {
			return false;
		}

		foreach ( $exports as $preset_name => $export_data ) {
			$filename = basename( $export_data['path'] );
			$zip->addFile( $export_data['path'], $filename );
		}

		$zip->close();

		return $zip_path;
	}

	/**
	 * Optimize image for web/social (reduce file size)
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $quality Quality (1-100).
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function optimize_for_web( int $attachment_id, int $quality = 85 ) {
		$image = self::get_image_editor_instance( $attachment_id );
		
		if ( ! $image || is_wp_error( $image ) ) {
			return false;
		}

		// Set quality
		$image->set_quality( $quality );

		return self::save_image( $image, $attachment_id, 'optimized-q' . $quality );
	}
}

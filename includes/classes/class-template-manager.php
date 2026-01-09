<?php
/**
 * Template Manager
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
 * Manages branded image templates
 */
class Template_Manager {

	/**
	 * Template option name
	 */
	const OPTION_NAME = 'timu_media_templates';

	/**
	 * Get all saved templates
	 *
	 * @return array Templates.
	 */
	public static function get_templates(): array {
		$templates = get_option( self::OPTION_NAME, array() );
		return is_array( $templates ) ? $templates : array();
	}

	/**
	 * Save a new template
	 *
	 * @param string $name Template name.
	 * @param array  $settings Template settings.
	 * @return bool Success.
	 */
	public static function save_template( string $name, array $settings ): bool {
		$templates = self::get_templates();
		
		$template_id = sanitize_title( $name );
		$templates[ $template_id ] = array(
			'name'       => $name,
			'settings'   => $settings,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		);

		return update_option( self::OPTION_NAME, $templates );
	}

	/**
	 * Get a specific template
	 *
	 * @param string $template_id Template ID.
	 * @return array|false Template data or false.
	 */
	public static function get_template( string $template_id ) {
		$templates = self::get_templates();
		return $templates[ $template_id ] ?? false;
	}

	/**
	 * Delete a template
	 *
	 * @param string $template_id Template ID.
	 * @return bool Success.
	 */
	public static function delete_template( string $template_id ): bool {
		$templates = self::get_templates();
		
		if ( ! array_key_exists( $template_id, $templates ) ) {
			return false;
		}

		unset( $templates[ $template_id ] );
		return update_option( self::OPTION_NAME, $templates );
	}

	/**
	 * Apply template to an image
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $template_id Template ID.
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function apply_template( int $attachment_id, string $template_id ) {
		$template = self::get_template( $template_id );
		
		if ( ! $template ) {
			return false;
		}

		$settings = $template['settings'];
		$image    = Image_Processor::get_image_editor_instance( $attachment_id );
		
		if ( ! $image || is_wp_error( $image ) ) {
			return false;
		}

		// Apply template settings in order
		$current_id = $attachment_id;
		$results    = array();

		// Apply filter if specified
		if ( ! empty( $settings['filter'] ) ) {
			$result = Filters_Manager::apply_filter( $current_id, $settings['filter'] );
			if ( $result ) {
				$results['filter'] = $result;
			}
		}

		// Apply crop if specified
		if ( ! empty( $settings['crop_preset'] ) ) {
			$result = Crop_Manager::crop_to_preset( $current_id, $settings['crop_preset'] );
			if ( $result ) {
				$results['crop'] = $result;
			}
		}

		// Apply text overlay if specified
		if ( ! empty( $settings['text'] ) ) {
			$text_options = array(
				'font_size'  => $settings['text_size'] ?? 48,
				'font_color' => $settings['text_color'] ?? '#FFFFFF',
				'bg_color'   => $settings['text_bg_color'] ?? '#000000',
				'bg_opacity' => $settings['text_bg_opacity'] ?? 0.5,
				'position'   => $settings['text_position'] ?? 'bottom',
			);
			$result = Text_Overlay_Manager::add_text_overlay( $current_id, $settings['text'], $text_options );
			if ( $result ) {
				$results['text'] = $result;
			}
		}

		// Apply watermark if specified
		if ( ! empty( $settings['watermark_id'] ) ) {
			$watermark_position = $settings['watermark_position'] ?? 'bottom-right';
			$watermark_opacity  = $settings['watermark_opacity'] ?? 80;
			$result = Watermark_Manager::add_watermark( $current_id, (int) $settings['watermark_id'], $watermark_position, $watermark_opacity );
			if ( $result ) {
				$results['watermark'] = $result;
			}
		}

		// Return the last successful result or false
		return ! empty( $results ) ? end( $results ) : false;
	}

	/**
	 * Create default templates
	 *
	 * @return void
	 */
	public static function create_default_templates(): void {
		$templates = self::get_templates();
		
		// Only create if no templates exist
		if ( empty( $templates ) ) {
			// Instagram Square Template
			self::save_template(
				'Instagram Square Post',
				array(
					'crop_preset' => 'instagram_square',
					'filter'      => 'warm',
				)
			);

			// Instagram Story Template
			self::save_template(
				'Instagram Story',
				array(
					'crop_preset' => 'instagram_story',
					'filter'      => 'high_contrast',
				)
			);

			// Facebook Post Template
			self::save_template(
				'Facebook Post',
				array(
					'crop_preset' => 'facebook_post',
				)
			);
		}
	}
}

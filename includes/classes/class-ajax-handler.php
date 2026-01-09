<?php
/**
 * AJAX Handler for Media Support Features
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
 * Handles AJAX requests for image processing
 */
class Ajax_Handler {

	/**
	 * Initialize AJAX handlers
	 */
	public static function init(): void {
		add_action( 'wp_ajax_timu_apply_filter', array( __CLASS__, 'apply_filter' ) );
		add_action( 'wp_ajax_timu_crop_image', array( __CLASS__, 'crop_image' ) );
		add_action( 'wp_ajax_timu_add_text_overlay', array( __CLASS__, 'add_text_overlay' ) );
		add_action( 'wp_ajax_timu_add_watermark', array( __CLASS__, 'add_watermark' ) );
		add_action( 'wp_ajax_timu_generate_hashtags', array( __CLASS__, 'generate_hashtags' ) );
		add_action( 'wp_ajax_timu_generate_caption', array( __CLASS__, 'generate_caption' ) );
		add_action( 'wp_ajax_timu_export_multi_platform', array( __CLASS__, 'export_multi_platform' ) );
		add_action( 'wp_ajax_timu_generate_preview', array( __CLASS__, 'generate_preview' ) );
		add_action( 'wp_ajax_timu_apply_template', array( __CLASS__, 'apply_template' ) );
	}

	/**
	 * Apply filter AJAX handler
	 */
	public static function apply_filter(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$filter        = isset( $_POST['filter'] ) ? sanitize_text_field( wp_unslash( $_POST['filter'] ) ) : '';

		if ( ! $attachment_id || ! $filter ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = Filters_Manager::apply_filter( $attachment_id, $filter );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to apply filter', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Crop image AJAX handler
	 */
	public static function crop_image(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$preset        = isset( $_POST['preset'] ) ? sanitize_text_field( wp_unslash( $_POST['preset'] ) ) : '';

		if ( ! $attachment_id || ! $preset ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = Crop_Manager::crop_to_preset( $attachment_id, $preset );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to crop image', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Add text overlay AJAX handler
	 */
	public static function add_text_overlay(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$text          = isset( $_POST['text'] ) ? sanitize_text_field( wp_unslash( $_POST['text'] ) ) : '';
		$options       = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();

		if ( ! $attachment_id || ! $text ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		// Sanitize options
		$sanitized_options = array();
		if ( isset( $options['font_size'] ) ) {
			$sanitized_options['font_size'] = absint( $options['font_size'] );
		}
		if ( isset( $options['font_color'] ) ) {
			$sanitized_options['font_color'] = sanitize_hex_color( $options['font_color'] );
		}
		if ( isset( $options['position'] ) ) {
			$sanitized_options['position'] = sanitize_text_field( $options['position'] );
		}

		$result = Text_Overlay_Manager::add_text_overlay( $attachment_id, $text, $sanitized_options );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add text overlay', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Add watermark AJAX handler
	 */
	public static function add_watermark(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$watermark_id  = isset( $_POST['watermark_id'] ) ? absint( $_POST['watermark_id'] ) : 0;
		$position      = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : 'bottom-right';
		$opacity       = isset( $_POST['opacity'] ) ? absint( $_POST['opacity'] ) : 80;

		if ( ! $attachment_id || ! $watermark_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = Watermark_Manager::add_watermark( $attachment_id, $watermark_id, $position, $opacity );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add watermark', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Generate hashtags AJAX handler
	 */
	public static function generate_hashtags(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$category      = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : 'general';
		$count         = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 10;

		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$hashtags = Hashtag_Generator::generate_hashtags( $attachment_id, $category, $count );

		wp_send_json_success( array( 'hashtags' => $hashtags ) );
	}

	/**
	 * Generate caption AJAX handler
	 */
	public static function generate_caption(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$style         = isset( $_POST['style'] ) ? sanitize_text_field( wp_unslash( $_POST['style'] ) ) : 'medium';

		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$caption = Hashtag_Generator::generate_caption( $attachment_id, $style );

		wp_send_json_success( array( 'caption' => $caption ) );
	}

	/**
	 * Export multi-platform AJAX handler
	 */
	public static function export_multi_platform(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$platforms     = isset( $_POST['platforms'] ) ? wp_unslash( $_POST['platforms'] ) : array();

		if ( ! $attachment_id || empty( $platforms ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		// Sanitize platforms
		$platforms = array_map( 'sanitize_text_field', (array) $platforms );

		$result = Export_Manager::export_multi_platform( $attachment_id, $platforms );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to export', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Generate preview AJAX handler
	 */
	public static function generate_preview(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$platform      = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : '';

		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		if ( empty( $platform ) ) {
			// Generate all previews
			$result = Preview_Simulator::generate_all_previews( $attachment_id );
		} else {
			// Generate specific platform preview
			$result = Preview_Simulator::generate_preview( $attachment_id, $platform );
		}

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to generate preview', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}

	/**
	 * Apply template AJAX handler
	 */
	public static function apply_template(): void {
		check_ajax_referer( 'timu-media-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$template_id   = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';

		if ( ! $attachment_id || ! $template_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = Template_Manager::apply_template( $attachment_id, $template_id );

		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to apply template', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}
	}
}

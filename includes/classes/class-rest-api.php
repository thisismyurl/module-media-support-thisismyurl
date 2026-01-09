<?php
/**
 * REST API Endpoints
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
 * REST API endpoints for image processing
 */
class REST_API {

	/**
	 * API namespace
	 */
	const NAMESPACE = 'timu-media/v1';

	/**
	 * Register REST API routes
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/filters',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_filters' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/filters/apply',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'apply_filter' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'filter'        => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/crop/presets',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_crop_presets' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/crop/apply',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'crop_image' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'preset'        => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/hashtags/generate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'generate_hashtags' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'category'      => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'general',
					),
					'count'         => array(
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'default'           => 10,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/preview',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'generate_preview' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'platform'      => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/templates',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_templates' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/templates/apply',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'apply_template' ),
				'permission_callback' => array( __CLASS__, 'permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'template_id'   => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Permission check
	 *
	 * @return bool
	 */
	public static function permissions_check(): bool {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Get available filters
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_filters(): \WP_REST_Response {
		$filters = Filters_Manager::get_filters();
		return new \WP_REST_Response( $filters, 200 );
	}

	/**
	 * Apply filter
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function apply_filter( \WP_REST_Request $request ): \WP_REST_Response {
		$attachment_id = $request->get_param( 'attachment_id' );
		$filter        = $request->get_param( 'filter' );

		$result = Filters_Manager::apply_filter( $attachment_id, $filter );

		if ( $result ) {
			return new \WP_REST_Response( $result, 200 );
		}

		return new \WP_REST_Response(
			array( 'message' => __( 'Failed to apply filter', TIMU_MEDIA_TEXT_DOMAIN ) ),
			400
		);
	}

	/**
	 * Get crop presets
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_crop_presets(): \WP_REST_Response {
		$presets = Crop_Manager::get_presets();
		return new \WP_REST_Response( $presets, 200 );
	}

	/**
	 * Crop image
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function crop_image( \WP_REST_Request $request ): \WP_REST_Response {
		$attachment_id = $request->get_param( 'attachment_id' );
		$preset        = $request->get_param( 'preset' );

		$result = Crop_Manager::crop_to_preset( $attachment_id, $preset );

		if ( $result ) {
			return new \WP_REST_Response( $result, 200 );
		}

		return new \WP_REST_Response(
			array( 'message' => __( 'Failed to crop image', TIMU_MEDIA_TEXT_DOMAIN ) ),
			400
		);
	}

	/**
	 * Generate hashtags
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function generate_hashtags( \WP_REST_Request $request ): \WP_REST_Response {
		$attachment_id = $request->get_param( 'attachment_id' );
		$category      = $request->get_param( 'category' );
		$count         = $request->get_param( 'count' );

		$hashtags = Hashtag_Generator::generate_hashtags( $attachment_id, $category, $count );

		return new \WP_REST_Response( array( 'hashtags' => $hashtags ), 200 );
	}

	/**
	 * Generate preview
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function generate_preview( \WP_REST_Request $request ): \WP_REST_Response {
		$attachment_id = $request->get_param( 'attachment_id' );
		$platform      = $request->get_param( 'platform' );

		if ( empty( $platform ) ) {
			$result = Preview_Simulator::generate_all_previews( $attachment_id );
		} else {
			$result = Preview_Simulator::generate_preview( $attachment_id, $platform );
		}

		if ( $result ) {
			return new \WP_REST_Response( $result, 200 );
		}

		return new \WP_REST_Response(
			array( 'message' => __( 'Failed to generate preview', TIMU_MEDIA_TEXT_DOMAIN ) ),
			400
		);
	}

	/**
	 * Get templates
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_templates(): \WP_REST_Response {
		$templates = Template_Manager::get_templates();
		return new \WP_REST_Response( $templates, 200 );
	}

	/**
	 * Apply template
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function apply_template( \WP_REST_Request $request ): \WP_REST_Response {
		$attachment_id = $request->get_param( 'attachment_id' );
		$template_id   = $request->get_param( 'template_id' );

		$result = Template_Manager::apply_template( $attachment_id, $template_id );

		if ( $result ) {
			return new \WP_REST_Response( $result, 200 );
		}

		return new \WP_REST_Response(
			array( 'message' => __( 'Failed to apply template', TIMU_MEDIA_TEXT_DOMAIN ) ),
			400
		);
	}
}

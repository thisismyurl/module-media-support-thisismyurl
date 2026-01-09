<?php
/**
 * Canva Integration Class
 *
 * Handles integration with Canva API for design creation, import, and sync.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport\Canva;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canva Integration Class
 */
class Canva_Integration {
	/**
	 * Canva API base URL
	 */
	private const API_BASE_URL = 'https://api.canva.com/v1';

	/**
	 * Instance of this class
	 *
	 * @var Canva_Integration|null
	 */
	private static ?Canva_Integration $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Canva_Integration
	 */
	public static function get_instance(): Canva_Integration {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_timu_canva_create', array( $this, 'ajax_create_design' ) );
		add_action( 'wp_ajax_timu_canva_import', array( $this, 'ajax_import_design' ) );
		add_action( 'wp_ajax_timu_canva_sync', array( $this, 'ajax_sync_designs' ) );
		add_action( 'wp_ajax_timu_canva_get_templates', array( $this, 'ajax_get_templates' ) );
		add_action( 'wp_ajax_timu_canva_get_brand_kit', array( $this, 'ajax_get_brand_kit' ) );
		add_action( 'wp_ajax_timu_canva_edit', array( $this, 'ajax_edit_design' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_canva_edit_button' ), 10, 2 );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( string $hook ): void {
		if ( 'upload.php' !== $hook && 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'timu-canva-integration',
			TIMU_MEDIA_URL . 'assets/css/canva-integration.css',
			array(),
			TIMU_MEDIA_VERSION
		);

		wp_enqueue_script(
			'timu-canva-integration',
			TIMU_MEDIA_URL . 'assets/js/canva-integration.js',
			array( 'jquery', 'media-editor' ),
			TIMU_MEDIA_VERSION,
			true
		);

		wp_localize_script(
			'timu-canva-integration',
			'timuCanva',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'timu-canva-nonce' ),
				'createLabel'       => __( 'Create in Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'editLabel'         => __( 'Edit in Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'syncLabel'         => __( 'Sync from Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'templatesLabel'    => __( 'Browse Templates', TIMU_MEDIA_TEXT_DOMAIN ),
				'isConnected'       => $this->is_connected(),
				'connectUrl'        => $this->get_connect_url(),
			)
		);
	}

	/**
	 * Check if Canva is connected
	 *
	 * @return bool
	 */
	public function is_connected(): bool {
		$access_token = get_option( 'timu_canva_access_token' );
		return ! empty( $access_token );
	}

	/**
	 * Get Canva OAuth connect URL
	 *
	 * @return string
	 */
	public function get_connect_url(): string {
		$client_id = get_option( 'timu_canva_client_id' );
		if ( empty( $client_id ) ) {
			return admin_url( 'admin.php?page=timu-core-support&tab=media-support-thisismyurl' );
		}

		$redirect_uri = admin_url( 'admin.php?page=timu-canva-oauth-callback' );
		$state = wp_create_nonce( 'timu-canva-oauth' );

		return add_query_arg(
			array(
				'client_id'     => $client_id,
				'redirect_uri'  => urlencode( $redirect_uri ),
				'response_type' => 'code',
				'scope'         => 'design:read design:write design:content:read design:content:write folder:read brand:read',
				'state'         => $state,
			),
			'https://www.canva.com/api/oauth/authorize'
		);
	}

	/**
	 * AJAX handler for creating a new design
	 */
	public function ajax_create_design(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$design_type = isset( $_POST['design_type'] ) ? sanitize_text_field( wp_unslash( $_POST['design_type'] ) ) : 'Document';

		$result = $this->create_design( $design_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Create a new design in Canva
	 *
	 * @param string $design_type Type of design to create.
	 * @return array|\WP_Error Design data or error.
	 */
	public function create_design( string $design_type = 'Document' ) {
		if ( ! $this->is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Canva account not connected', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$access_token = get_option( 'timu_canva_access_token' );

		$response = wp_remote_post(
			self::API_BASE_URL . '/designs',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'design_type' => $design_type,
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code && 201 !== $code ) {
			return new \WP_Error( 'api_error', $body['message'] ?? __( 'Failed to create design', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		return $body;
	}

	/**
	 * AJAX handler for importing a design
	 */
	public function ajax_import_design(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$design_id = isset( $_POST['design_id'] ) ? sanitize_text_field( wp_unslash( $_POST['design_id'] ) ) : '';

		if ( empty( $design_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Design ID is required', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = $this->import_design( $design_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Import a design from Canva to WordPress media library
	 *
	 * @param string $design_id Canva design ID.
	 * @param bool   $auto_optimize Whether to auto-optimize the image.
	 * @return array|\WP_Error Import result or error.
	 */
	public function import_design( string $design_id, bool $auto_optimize = true ) {
		if ( ! $this->is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Canva account not connected', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		// Get the design export URL.
		$export_url = $this->get_design_export_url( $design_id );
		if ( is_wp_error( $export_url ) ) {
			return $export_url;
		}

		// Download the image.
		$tmp_file = download_url( $export_url );
		if ( is_wp_error( $tmp_file ) ) {
			return $tmp_file;
		}

		// Get design metadata.
		$design_info = $this->get_design_info( $design_id );
		$filename = is_wp_error( $design_info ) ? 'canva-design-' . $design_id . '.png' : sanitize_file_name( $design_info['name'] ?? 'canva-design' ) . '.png';

		// Prepare file array for media_handle_sideload.
		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp_file,
		);

		// Import to media library.
		$attachment_id = media_handle_sideload( $file_array, 0 );

		if ( is_wp_error( $attachment_id ) ) {
			// Clean up the temporary file with proper path validation.
			if ( file_exists( $tmp_file ) ) {
				$normalized_tmp = wp_normalize_path( $tmp_file );
				$temp_dir = wp_normalize_path( get_temp_dir() );
				// Only delete if file is actually in temp directory.
				if ( 0 === strpos( $normalized_tmp, $temp_dir ) ) {
					unlink( $tmp_file );
				}
			}
			return $attachment_id;
		}

		// Store Canva metadata.
		update_post_meta( $attachment_id, '_canva_design_id', $design_id );
		update_post_meta( $attachment_id, '_canva_imported', current_time( 'mysql' ) );

		// Auto-optimize if enabled.
		if ( $auto_optimize ) {
			$this->optimize_imported_image( $attachment_id );
		}

		return array(
			'attachment_id' => $attachment_id,
			'url'           => wp_get_attachment_url( $attachment_id ),
		);
	}

	/**
	 * Get design export URL
	 *
	 * @param string $design_id Canva design ID.
	 * @return string|\WP_Error Export URL or error.
	 */
	private function get_design_export_url( string $design_id ) {
		$access_token = get_option( 'timu_canva_access_token' );

		$response = wp_remote_post(
			self::API_BASE_URL . '/designs/' . $design_id . '/export',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'format' => 'png',
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error( 'export_error', $body['message'] ?? __( 'Failed to export design', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		return $body['url'] ?? new \WP_Error( 'no_url', __( 'No export URL returned', TIMU_MEDIA_TEXT_DOMAIN ) );
	}

	/**
	 * Get design information
	 *
	 * @param string $design_id Canva design ID.
	 * @return array|\WP_Error Design info or error.
	 */
	private function get_design_info( string $design_id ) {
		$access_token = get_option( 'timu_canva_access_token' );

		$response = wp_remote_get(
			self::API_BASE_URL . '/designs/' . $design_id,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error( 'info_error', $body['message'] ?? __( 'Failed to get design info', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		return $body;
	}

	/**
	 * Optimize imported image
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	private function optimize_imported_image( int $attachment_id ): void {
		// Convert to WebP if possible.
		$file = get_attached_file( $attachment_id );
		if ( ! $file ) {
			return;
		}

		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) ) {
			return;
		}

		// Check if WebP is supported by the image editor.
		if ( ! $editor->supports_mime_type( 'image/webp' ) ) {
			// Generate responsive sizes with original format.
			wp_generate_attachment_metadata( $attachment_id, $file );
			return;
		}

		// Save as WebP.
		$webp_file = str_replace( '.png', '.webp', $file );
		$saved = $editor->save( $webp_file, 'image/webp' );
		
		if ( ! is_wp_error( $saved ) && isset( $saved['path'] ) ) {
			update_attached_file( $attachment_id, $saved['path'] );
			wp_update_post(
				array(
					'ID'             => $attachment_id,
					'post_mime_type' => 'image/webp',
				)
			);

			// Clean up the original PNG file after successful WebP conversion.
			if ( file_exists( $file ) && $file !== $saved['path'] ) {
				wp_delete_file( $file );
			}
		}

		// Generate responsive sizes.
		wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );
	}

	/**
	 * AJAX handler for syncing designs
	 */
	public function ajax_sync_designs(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = $this->sync_designs();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Sync designs from Canva
	 *
	 * @return array|\WP_Error Sync results or error.
	 */
	public function sync_designs() {
		if ( ! $this->is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Canva account not connected', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$access_token = get_option( 'timu_canva_access_token' );

		$response = wp_remote_get(
			self::API_BASE_URL . '/designs',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error( 'sync_error', $body['message'] ?? __( 'Failed to sync designs', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$synced = array();
		$designs = $body['items'] ?? array();

		foreach ( $designs as $design ) {
			$design_id = $design['id'] ?? '';
			if ( empty( $design_id ) ) {
				continue;
			}

			// Check if already imported.
			$existing = $this->find_imported_design( $design_id );
			if ( $existing ) {
				// Update if auto-update is enabled.
				if ( get_option( 'timu_canva_auto_update', false ) ) {
					$result = $this->import_design( $design_id );
					if ( ! is_wp_error( $result ) ) {
						$synced[] = $design_id;
					}
				}
			}
		}

		return array(
			'synced' => count( $synced ),
			'total'  => count( $designs ),
		);
	}

	/**
	 * Find imported design by Canva design ID
	 *
	 * @param string $design_id Canva design ID.
	 * @return int|false Attachment ID or false.
	 */
	private function find_imported_design( string $design_id ) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => '_canva_design_id',
						'value' => $design_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		return ! empty( $query->posts ) ? $query->posts[0] : false;
	}

	/**
	 * AJAX handler for getting templates
	 */
	public function ajax_get_templates(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';

		$result = $this->get_templates( $category );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get Canva templates
	 *
	 * @param string $category Template category.
	 * @return array|\WP_Error Templates or error.
	 */
	public function get_templates( string $category = '' ) {
		if ( ! $this->is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Canva account not connected', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		// For now, return sample templates as Canva API might require specific access.
		$templates = array(
			'social_posts'     => array(
				array(
					'id'    => 'template-social-1',
					'name'  => 'Instagram Post',
					'thumb' => TIMU_MEDIA_URL . 'assets/images/template-instagram.png',
				),
				array(
					'id'    => 'template-social-2',
					'name'  => 'Facebook Post',
					'thumb' => TIMU_MEDIA_URL . 'assets/images/template-facebook.png',
				),
			),
			'blog_graphics'    => array(
				array(
					'id'    => 'template-blog-1',
					'name'  => 'Blog Header',
					'thumb' => TIMU_MEDIA_URL . 'assets/images/template-blog-header.png',
				),
			),
			'infographics'     => array(
				array(
					'id'    => 'template-infographic-1',
					'name'  => 'Process Infographic',
					'thumb' => TIMU_MEDIA_URL . 'assets/images/template-infographic.png',
				),
			),
		);

		if ( ! empty( $category ) && isset( $templates[ $category ] ) ) {
			return $templates[ $category ];
		}

		return $templates;
	}

	/**
	 * AJAX handler for getting brand kit
	 */
	public function ajax_get_brand_kit(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$result = $this->get_brand_kit();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get Canva brand kit
	 *
	 * @return array|\WP_Error Brand kit data or error.
	 */
	public function get_brand_kit() {
		if ( ! $this->is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Canva account not connected', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$access_token = get_option( 'timu_canva_access_token' );

		$response = wp_remote_get(
			self::API_BASE_URL . '/brand-kits',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error( 'brand_kit_error', $body['message'] ?? __( 'Failed to get brand kit', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		// Cache the brand kit.
		update_option( 'timu_canva_brand_kit', $body );

		return $body;
	}

	/**
	 * AJAX handler for editing a design
	 */
	public function ajax_edit_design(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;

		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Attachment ID is required', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$design_id = get_post_meta( $attachment_id, '_canva_design_id', true );

		if ( empty( $design_id ) ) {
			wp_send_json_error( array( 'message' => __( 'This attachment is not linked to a Canva design', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$edit_url = $this->get_edit_url( $design_id );

		wp_send_json_success( array( 'edit_url' => $edit_url ) );
	}

	/**
	 * Get edit URL for a Canva design
	 *
	 * @param string $design_id Canva design ID.
	 * @return string Edit URL.
	 */
	private function get_edit_url( string $design_id ): string {
		return 'https://www.canva.com/design/' . $design_id . '/edit';
	}

	/**
	 * Add "Edit in Canva" button to attachment fields
	 *
	 * @param array    $form_fields Form fields.
	 * @param \WP_Post $post Post object.
	 * @return array Modified form fields.
	 */
	public function add_canva_edit_button( array $form_fields, \WP_Post $post ): array {
		$design_id = get_post_meta( $post->ID, '_canva_design_id', true );

		if ( ! empty( $design_id ) ) {
			$edit_url = $this->get_edit_url( $design_id );
			$form_fields['canva_edit'] = array(
				'label' => __( 'Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'input' => 'html',
				'html'  => sprintf(
					'<a href="%s" target="_blank" class="button button-secondary">%s</a>',
					esc_url( $edit_url ),
					esc_html__( 'Edit in Canva', TIMU_MEDIA_TEXT_DOMAIN )
				),
			);
		}

		return $form_fields;
	}
}

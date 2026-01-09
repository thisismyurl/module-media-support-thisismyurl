<?php
/**
 * Canva OAuth Handler
 *
 * Handles OAuth2 authentication flow with Canva.
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
 * OAuth Handler Class
 */
class OAuth_Handler {
	/**
	 * Instance of this class
	 *
	 * @var OAuth_Handler|null
	 */
	private static ?OAuth_Handler $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return OAuth_Handler
	 */
	public static function get_instance(): OAuth_Handler {
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
		add_action( 'admin_menu', array( $this, 'add_oauth_callback_page' ) );
		add_action( 'admin_init', array( $this, 'handle_oauth_callback' ) );
		add_action( 'wp_ajax_timu_canva_disconnect', array( $this, 'ajax_disconnect' ) );
	}

	/**
	 * Add OAuth callback page (hidden)
	 */
	public function add_oauth_callback_page(): void {
		add_submenu_page(
			null, // Hidden page.
			__( 'Canva OAuth Callback', TIMU_MEDIA_TEXT_DOMAIN ),
			'',
			'manage_options',
			'timu-canva-oauth-callback',
			array( $this, 'render_oauth_callback' )
		);
	}

	/**
	 * Handle OAuth callback
	 */
	public function handle_oauth_callback(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from Canva.
		if ( ! isset( $_GET['page'] ) || 'timu-canva-oauth-callback' !== $_GET['page'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from Canva.
		if ( ! isset( $_GET['code'] ) ) {
			return;
		}

		// Verify state.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from Canva.
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		if ( ! wp_verify_nonce( $state, 'timu-canva-oauth' ) ) {
			wp_die( esc_html__( 'Invalid state parameter', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from Canva.
		$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );

		// Exchange code for access token.
		$result = $this->exchange_code_for_token( $code );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		// Store access token.
		update_option( 'timu_canva_access_token', $result['access_token'] );
		if ( isset( $result['refresh_token'] ) ) {
			update_option( 'timu_canva_refresh_token', $result['refresh_token'] );
		}
		if ( isset( $result['expires_in'] ) ) {
			update_option( 'timu_canva_token_expires', time() + $result['expires_in'] );
		}

		// Redirect to settings page.
		wp_safe_redirect( admin_url( 'admin.php?page=timu-core-support&tab=media-support-thisismyurl&canva-connected=1' ) );
		exit;
	}

	/**
	 * Exchange authorization code for access token
	 *
	 * @param string $code Authorization code.
	 * @return array|\WP_Error Token data or error.
	 */
	private function exchange_code_for_token( string $code ) {
		$client_id = get_option( 'timu_canva_client_id' );
		$client_secret = get_option( 'timu_canva_client_secret' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return new \WP_Error( 'missing_credentials', __( 'Canva API credentials not configured', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$redirect_uri = admin_url( 'admin.php?page=timu-canva-oauth-callback' );

		$response = wp_remote_post(
			'https://api.canva.com/oauth/token',
			array(
				'body'    => array(
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'redirect_uri'  => $redirect_uri,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code_response = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code_response ) {
			return new \WP_Error( 'token_error', $body['error_description'] ?? __( 'Failed to get access token', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		return $body;
	}

	/**
	 * Render OAuth callback page
	 */
	public function render_oauth_callback(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Connecting to Canva...', TIMU_MEDIA_TEXT_DOMAIN ); ?></h1>
			<p><?php esc_html_e( 'Please wait while we connect your Canva account.', TIMU_MEDIA_TEXT_DOMAIN ); ?></p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for disconnecting Canva
	 */
	public function ajax_disconnect(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		delete_option( 'timu_canva_access_token' );
		delete_option( 'timu_canva_refresh_token' );
		delete_option( 'timu_canva_token_expires' );

		wp_send_json_success( array( 'message' => __( 'Canva account disconnected', TIMU_MEDIA_TEXT_DOMAIN ) ) );
	}

	/**
	 * Refresh access token
	 *
	 * @return bool|\WP_Error True on success, error on failure.
	 */
	public function refresh_token() {
		$refresh_token = get_option( 'timu_canva_refresh_token' );

		if ( empty( $refresh_token ) ) {
			return new \WP_Error( 'no_refresh_token', __( 'No refresh token available', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		$client_id = get_option( 'timu_canva_client_id' );
		$client_secret = get_option( 'timu_canva_client_secret' );

		$response = wp_remote_post(
			'https://api.canva.com/oauth/token',
			array(
				'body'    => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
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
			return new \WP_Error( 'refresh_error', $body['error_description'] ?? __( 'Failed to refresh token', TIMU_MEDIA_TEXT_DOMAIN ) );
		}

		// Update tokens.
		update_option( 'timu_canva_access_token', $body['access_token'] );
		if ( isset( $body['refresh_token'] ) ) {
			update_option( 'timu_canva_refresh_token', $body['refresh_token'] );
		}
		if ( isset( $body['expires_in'] ) ) {
			update_option( 'timu_canva_token_expires', time() + $body['expires_in'] );
		}

		return true;
	}
}

<?php
/**
 * Canva Sync Manager
 *
 * Handles real-time sync and scheduled updates for Canva designs.
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
 * Sync Manager Class
 */
class Sync_Manager {
	/**
	 * Instance of this class
	 *
	 * @var Sync_Manager|null
	 */
	private static ?Sync_Manager $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Sync_Manager
	 */
	public static function get_instance(): Sync_Manager {
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
		add_action( 'timu_canva_sync_designs', array( $this, 'run_sync' ) );
		add_action( 'init', array( $this, 'schedule_sync' ) );
		add_action( 'wp_ajax_timu_canva_manual_sync', array( $this, 'ajax_manual_sync' ) );
	}

	/**
	 * Schedule automatic sync
	 */
	public function schedule_sync(): void {
		if ( ! wp_next_scheduled( 'timu_canva_sync_designs' ) ) {
			// Schedule hourly sync if auto-sync is enabled.
			if ( get_option( 'timu_canva_auto_sync', false ) ) {
				wp_schedule_event( time(), 'hourly', 'timu_canva_sync_designs' );
			}
		} elseif ( ! get_option( 'timu_canva_auto_sync', false ) ) {
			// Clear scheduled event if auto-sync is disabled.
			wp_clear_scheduled_hook( 'timu_canva_sync_designs' );
		}
	}

	/**
	 * Run sync process
	 */
	public function run_sync(): void {
		$canva = Canva_Integration::get_instance();

		if ( ! $canva->is_connected() ) {
			return;
		}

		$result = $canva->sync_designs();

		if ( is_wp_error( $result ) ) {
			error_log( 'Canva sync error: ' . $result->get_error_message() );
			return;
		}

		// Store last sync time.
		update_option( 'timu_canva_last_sync', current_time( 'mysql' ) );

		// Log sync results.
		update_option( 'timu_canva_last_sync_results', $result );
	}

	/**
	 * AJAX handler for manual sync
	 */
	public function ajax_manual_sync(): void {
		check_ajax_referer( 'timu-canva-nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', TIMU_MEDIA_TEXT_DOMAIN ) ) );
		}

		$this->run_sync();

		$last_results = get_option( 'timu_canva_last_sync_results', array() );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %1$d: synced count, %2$d: total count */
					__( 'Synced %1$d of %2$d designs', TIMU_MEDIA_TEXT_DOMAIN ),
					$last_results['synced'] ?? 0,
					$last_results['total'] ?? 0
				),
				'results' => $last_results,
			)
		);
	}

	/**
	 * Get sync status
	 *
	 * @return array Sync status information.
	 */
	public function get_sync_status(): array {
		$last_sync = get_option( 'timu_canva_last_sync' );
		$last_results = get_option( 'timu_canva_last_sync_results', array() );
		$auto_sync = get_option( 'timu_canva_auto_sync', false );
		$next_sync = wp_next_scheduled( 'timu_canva_sync_designs' );

		return array(
			'last_sync'    => $last_sync,
			'last_results' => $last_results,
			'auto_sync'    => $auto_sync,
			'next_sync'    => $next_sync ? wp_date( 'Y-m-d H:i:s', $next_sync ) : null,
		);
	}
}

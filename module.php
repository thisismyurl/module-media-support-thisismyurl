<?php
/**
 * Media Hub Module
 *
 * This module is loaded by the TIMU Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of Core.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TIMU_MEDIA_VERSION', '1.2601.0819' );
define( 'TIMU_MEDIA_FILE', __FILE__ );
define( 'TIMU_MEDIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIMU_MEDIA_URL', plugin_dir_url( __FILE__ ) );
define( 'TIMU_MEDIA_BASENAME', plugin_basename( __FILE__ ) );
define( 'TIMU_MEDIA_TEXT_DOMAIN', 'media-support-thisismyurl' );
define( 'TIMU_MEDIA_MIN_PHP', '8.1.29' );
define( 'TIMU_MEDIA_MIN_WP', '6.4.0' );
define( 'TIMU_SUITE_ID', 'thisismyurl-media-suite-2026' );
define( 'TIMU_MEDIA_REQUIRES_CORE', 'core-support-thisismyurl/core-support-thisismyurl.php' );

/**
 * Autoloader for Canva and Design Hub classes.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix = 'TIMU\\MediaSupport\\';
		$base_dir = __DIR__ . '/includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$relative_class = str_replace( '\\', '/', $relative_class );
		$relative_class = strtolower( str_replace( '_', '-', $relative_class ) );

		$parts = explode( '/', $relative_class );
		$class_name = array_pop( $parts );
		$namespace_path = implode( '/', $parts );

		$file = $base_dir . $namespace_path . '/class-' . $class_name . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Initialize Media Support.
 */
function timu_media_init(): void {
	// Verify Core is present.
	if ( ! class_exists( '\\TIMU\\Core\\Spoke\\TIMU_Spoke_Base' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_media_missing_core_notice' );
		return;
	}

	// Register with Core module registry (Hub-level for media layer).
	do_action(
		'timu_register_module',
		array(
			'slug'         => 'media-support-thisismyurl',
			'name'         => __( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ),
			'type'         => 'hub',
			'suite'        => 'media',
			'version'      => TIMU_MEDIA_VERSION,
			'description'  => __( 'Media hub for non-image media processing, batching, and policies.', TIMU_MEDIA_TEXT_DOMAIN ),
			'capabilities' => array( 'media_hub', 'batch', 'policies' ),
			'path'         => TIMU_MEDIA_PATH,
			'url'          => TIMU_MEDIA_URL,
			'basename'     => TIMU_MEDIA_BASENAME,
		)
	);

	// Register media_hub feature for plugins that depend on media processing.
	if ( function_exists( '\TIMU\CoreSupport\register_timu_feature' ) ) {
		\TIMU\CoreSupport\register_timu_feature( 'media_hub', array(
			'plugin'      => 'media-support-thisismyurl',
			'name'        => __( 'Media Hub', TIMU_MEDIA_TEXT_DOMAIN ),
			'description' => __( 'Shared media optimization and processing infrastructure', TIMU_MEDIA_TEXT_DOMAIN ),
			'version'     => TIMU_MEDIA_VERSION,
		) );
	}

	// Minimal hub class extending Core Spoke Base (no subcomponents yet).
	if ( ! class_exists( __NAMESPACE__ . '\\TIMU_Media_Support' ) ) {
		class TIMU_Media_Support extends \TIMU\Core\Spoke\TIMU_Spoke_Base {
			public function __construct() {
				parent::__construct( 'media-support-thisismyurl', TIMU_MEDIA_URL, 'timu_media_settings_group', 'dashicons-admin-media', 'timu-core-support' );
				add_action( 'init', array( $this, 'setup_plugin' ), 20 );
				add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			}

			public function setup_plugin(): void {
				$this->is_licensed();
				$this->init_settings_generator(
					array(
						'hub_settings' => array(
							'title'       => __( 'Media Hub Settings', TIMU_MEDIA_TEXT_DOMAIN ),
							'description' => __( 'Central coordination for media (non-image) operations.', TIMU_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Media Hub', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Master switch for media processing.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
						'canva_settings' => array(
							'title'       => __( 'Canva Integration', TIMU_MEDIA_TEXT_DOMAIN ),
							'description' => __( 'Connect and configure Canva for creating and managing designs.', TIMU_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'client_id' => array(
									'type'        => 'text',
									'label'       => __( 'Canva Client ID', TIMU_MEDIA_TEXT_DOMAIN ),
									'description' => __( 'Enter your Canva API Client ID.', TIMU_MEDIA_TEXT_DOMAIN ),
									'option_name' => 'timu_canva_client_id',
								),
								'client_secret' => array(
									'type'        => 'password',
									'label'       => __( 'Canva Client Secret', TIMU_MEDIA_TEXT_DOMAIN ),
									'description' => __( 'Enter your Canva API Client Secret.', TIMU_MEDIA_TEXT_DOMAIN ),
									'option_name' => 'timu_canva_client_secret',
								),
								'auto_sync' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Auto-Sync', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Automatically sync designs from Canva hourly.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 0,
									'option_name'  => 'timu_canva_auto_sync',
								),
								'auto_update' => array(
									'type'         => 'toggle',
									'label'        => __( 'Auto-Update Designs', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Automatically update imported designs when they change in Canva.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 0,
									'option_name'  => 'timu_canva_auto_update',
								),
							),
						),
						'design_hub_settings' => array(
							'title'       => __( 'Design Hub', TIMU_MEDIA_TEXT_DOMAIN ),
							'description' => __( 'Enable integrations with multiple design platforms.', TIMU_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'canva_enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Canva', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Show Canva in Design Hub.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'option_name'  => 'timu_design_hub_canva_enabled',
								),
								'crello_enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Crello', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Show Crello in Design Hub.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 0,
									'option_name'  => 'timu_design_hub_crello_enabled',
								),
								'adobe_express_enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Adobe Express', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Show Adobe Express in Design Hub.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 0,
									'option_name'  => 'timu_design_hub_adobe_express_enabled',
								),
								'figma_enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Figma', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Show Figma in Design Hub.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 0,
									'option_name'  => 'timu_design_hub_figma_enabled',
								),
							),
						),
					)
				);
			}

			public function add_admin_menu(): void {
				$this->add_admin_submenu( strtoupper( __( 'Media', TIMU_MEDIA_TEXT_DOMAIN ) ) );
				add_submenu_page(
					'upload.php',
					__( 'Media Settings', TIMU_MEDIA_TEXT_DOMAIN ),
					__( 'Media Settings', TIMU_MEDIA_TEXT_DOMAIN ),
					'manage_options',
					'timu-media-settings-redirect',
					array( $this, 'render_media_settings_redirect' )
				);
			}

			public function render_media_settings_redirect(): void {
				$redirect_url = admin_url( 'admin.php?page=timu-core-support&tab=media-support-thisismyurl' );
				?>
				<script type="text/javascript">
					window.location.href = '<?php echo esc_url( $redirect_url ); ?>';
				</script>
				<?php
			}

			public function render_settings_page(): void {
				$this->render_settings_page_base( strtoupper( __( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ) ) );
			}
		}
	}

	new TIMU_Media_Support();

	// Initialize Canva Integration.
	if ( class_exists( __NAMESPACE__ . '\\Canva\\Canva_Integration' ) ) {
		Canva\Canva_Integration::get_instance();
		Canva\OAuth_Handler::get_instance();
		Canva\Sync_Manager::get_instance();
	}

	// Initialize Design Hub.
	if ( class_exists( __NAMESPACE__ . '\\DesignHub\\Design_Hub' ) ) {
		DesignHub\Design_Hub::get_instance();
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\timu_media_init', 12 );

/**
 * Admin notice for missing Core.
 */
function timu_media_missing_core_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Media Support requires Core Support to be installed and active.', TIMU_MEDIA_TEXT_DOMAIN )
	);
}

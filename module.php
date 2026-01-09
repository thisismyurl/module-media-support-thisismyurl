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
 * Initialize Media Support.
 */
function timu_media_init(): void {
	// Verify Core is present.
	if ( ! class_exists( '\\TIMU\\Core\\Spoke\\TIMU_Spoke_Base' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_media_missing_core_notice' );
		return;
	}

	// Load required classes.
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-image-processor.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-filters-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-crop-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-text-overlay-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-watermark-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-hashtag-generator.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-export-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-preview-simulator.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-template-manager.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-ajax-handler.php';
	require_once TIMU_MEDIA_PATH . 'includes/classes/class-rest-api.php';

	// Register with Core module registry (Hub-level for media layer).
	do_action(
		'timu_register_module',
		array(
			'slug'         => 'media-support-thisismyurl',
			'name'         => __( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ),
			'type'         => 'hub',
			'suite'        => 'media',
			'version'      => TIMU_MEDIA_VERSION,
			'description'  => __( 'Media hub for image processing, social optimization, and batching.', TIMU_MEDIA_TEXT_DOMAIN ),
			'capabilities' => array( 'media_hub', 'batch', 'policies', 'social_optimization', 'image_filters' ),
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

	// Initialize AJAX handlers.
	Ajax_Handler::init();

	// Register REST API routes.
	add_action( 'rest_api_init', array( 'TIMU\MediaSupport\REST_API', 'register_routes' ) );

	// Create default templates on activation.
	add_action( 'init', array( 'TIMU\MediaSupport\Template_Manager', 'create_default_templates' ), 999 );

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
							'description' => __( 'Central coordination for media operations including social optimization.', TIMU_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Media Hub', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Master switch for media processing.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_filters' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Image Filters', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Allow applying Instagram-style filters to images.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_social_crop' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Social Media Crop Presets', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Provide one-click crop for Instagram, Facebook, LinkedIn, etc.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_text_overlay' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Text Overlays', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Add text overlays to images.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_watermark' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Watermark Placement', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Add watermarks and logos to images.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_hashtags' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Hashtag Generator', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Generate hashtags and captions based on image content.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_export' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Multi-Platform Export', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Export images in platform-specific sizes.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_preview' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Social Preview Simulator', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Preview how images will look on social platforms.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_templates' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Branded Templates', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Save and apply reusable branded templates.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'default_watermark_id' => array(
									'type'         => 'text',
									'label'        => __( 'Default Watermark Attachment ID', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'The attachment ID of the default watermark/logo image.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => '',
									'globalizable' => true,
								),
							),
						),
					)
				);
				
				// Enqueue admin scripts and styles.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			}

			/**
			 * Enqueue admin assets
			 */
			public function enqueue_admin_assets( $hook ): void {
				// Only load on media-related pages.
				if ( ! in_array( $hook, array( 'upload.php', 'post.php', 'post-new.php' ), true ) ) {
					// Also check if we're in the media modal.
					global $pagenow;
					if ( ! in_array( $pagenow, array( 'upload.php', 'post.php', 'post-new.php', 'async-upload.php' ), true ) ) {
						return;
					}
				}

				// Enqueue JavaScript.
				wp_enqueue_script(
					'timu-media-enhancements',
					TIMU_MEDIA_URL . 'includes/admin/js/media-enhancements.js',
					array( 'jquery', 'media-editor', 'media-views' ),
					TIMU_MEDIA_VERSION,
					true
				);

				// Enqueue CSS.
				wp_enqueue_style(
					'timu-media-enhancements',
					TIMU_MEDIA_URL . 'includes/admin/css/media-enhancements.css',
					array(),
					TIMU_MEDIA_VERSION
				);

				// Localize script with data.
				wp_localize_script(
					'timu-media-enhancements',
					'timuMediaData',
					array(
						'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
						'nonce'       => wp_create_nonce( 'timu-media-nonce' ),
						'filters'     => Filters_Manager::get_filters(),
						'cropPresets' => Crop_Manager::get_presets(),
						'templates'   => Template_Manager::get_templates(),
						'i18n'        => array(
							'socialEnhancements' => __( 'Social Media Enhancements', TIMU_MEDIA_TEXT_DOMAIN ),
							'filters'            => __( 'Filters', TIMU_MEDIA_TEXT_DOMAIN ),
							'cropPresets'        => __( 'Social Crop Presets', TIMU_MEDIA_TEXT_DOMAIN ),
							'hashtags'           => __( 'Hashtags', TIMU_MEDIA_TEXT_DOMAIN ),
							'socialPreview'      => __( 'Social Preview', TIMU_MEDIA_TEXT_DOMAIN ),
							'templates'          => __( 'Templates', TIMU_MEDIA_TEXT_DOMAIN ),
							'generateHashtags'   => __( 'Generate Hashtags', TIMU_MEDIA_TEXT_DOMAIN ),
							'generatePreview'    => __( 'Generate Preview', TIMU_MEDIA_TEXT_DOMAIN ),
							'applyTemplate'      => __( 'Apply Template', TIMU_MEDIA_TEXT_DOMAIN ),
							'selectTemplate'     => __( 'Select Template', TIMU_MEDIA_TEXT_DOMAIN ),
							'processing'         => __( 'Processing...', TIMU_MEDIA_TEXT_DOMAIN ),
							'filterApplied'      => __( 'Filter applied! New image:', TIMU_MEDIA_TEXT_DOMAIN ),
							'cropApplied'        => __( 'Crop applied! New image:', TIMU_MEDIA_TEXT_DOMAIN ),
							'templateApplied'    => __( 'Template applied! New image:', TIMU_MEDIA_TEXT_DOMAIN ),
							'error'              => __( 'Error:', TIMU_MEDIA_TEXT_DOMAIN ),
							'noImageSelected'    => __( 'No image selected', TIMU_MEDIA_TEXT_DOMAIN ),
							'cropRequired'       => __( 'Crop required', TIMU_MEDIA_TEXT_DOMAIN ),
							'perfectFit'         => __( 'Perfect fit', TIMU_MEDIA_TEXT_DOMAIN ),
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

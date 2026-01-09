<?php
/**
 * Documents Support Module
 *
 * This module is loaded by the TIMU Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of the Media Hub.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_DOCUMENTS_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\DocumentsSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TIMU_DOCUMENTS_VERSION', '1.0.0' );
define( 'TIMU_DOCUMENTS_FILE', __FILE__ );
define( 'TIMU_DOCUMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIMU_DOCUMENTS_URL', plugin_dir_url( __FILE__ ) );
define( 'TIMU_DOCUMENTS_BASENAME', plugin_basename( __FILE__ ) );
define( 'TIMU_DOCUMENTS_TEXT_DOMAIN', 'documents-support-thisismyurl' );
define( 'TIMU_DOCUMENTS_MIN_PHP', '8.1.29' );
define( 'TIMU_DOCUMENTS_MIN_WP', '6.4.0' );
define( 'TIMU_DOCUMENTS_REQUIRES_HUB', 'media-support-thisismyurl' );
define( 'TIMU_DOCUMENTS_REQUIRES_CORE', 'core-support-thisismyurl/core-support-thisismyurl.php' );

/**
 * Initialize Documents Support.
 */
function timu_documents_init(): void {
	// Verify Core is present.
	if ( ! class_exists( '\\TIMU\\Core\\Spoke\\TIMU_Spoke_Base' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_documents_missing_core_notice' );
		return;
	}

	// Verify Media Hub is present.
	if ( ! defined( 'TIMU_MEDIA_VERSION' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_documents_missing_hub_notice' );
		return;
	}

	// Register with Core module registry (Spoke-level for documents).
	do_action(
		'timu_register_module',
		array(
			'slug'         => 'documents-support-thisismyurl',
			'name'         => __( 'Documents Support', TIMU_DOCUMENTS_TEXT_DOMAIN ),
			'type'         => 'spoke',
			'suite'        => 'media',
			'hub'          => 'media-support-thisismyurl',
			'version'      => TIMU_DOCUMENTS_VERSION,
			'description'  => __( 'Document processing, preview, search, and management for PDFs, DOCX, PPT, and more.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
			'capabilities' => array( 'document_processing', 'document_preview', 'document_search', 'document_management' ),
			'path'         => TIMU_DOCUMENTS_PATH,
			'url'          => TIMU_DOCUMENTS_URL,
			'basename'     => TIMU_DOCUMENTS_BASENAME,
		)
	);

	// Register documents feature for plugins that depend on document processing.
	if ( function_exists( '\TIMU\CoreSupport\register_timu_feature' ) ) {
		\TIMU\CoreSupport\register_timu_feature( 'documents', array(
			'plugin'      => 'documents-support-thisismyurl',
			'name'        => __( 'Documents Support', TIMU_DOCUMENTS_TEXT_DOMAIN ),
			'description' => __( 'Document processing and management infrastructure', TIMU_DOCUMENTS_TEXT_DOMAIN ),
			'version'     => TIMU_DOCUMENTS_VERSION,
		) );
	}

	// Documents Support class extending Core Spoke Base.
	if ( ! class_exists( __NAMESPACE__ . '\\TIMU_Documents_Support' ) ) {
		class TIMU_Documents_Support extends \TIMU\Core\Spoke\TIMU_Spoke_Base {
			/**
			 * Constructor.
			 */
			public function __construct() {
				parent::__construct( 'documents-support-thisismyurl', TIMU_DOCUMENTS_URL, 'timu_documents_settings_group', 'dashicons-media-document', 'timu-core-support' );
				add_action( 'init', array( $this, 'setup_plugin' ), 20 );
				add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			}

			/**
			 * Setup plugin functionality.
			 */
			public function setup_plugin(): void {
				$this->is_licensed();
				$this->init_settings_generator(
					array(
						'general_settings' => array(
							'title'       => __( 'General Settings', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Core document processing settings.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Documents Support', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Master switch for document processing features.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
						'preview_settings' => array(
							'title'       => __( 'Preview & Display', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Settings for document previews and inline viewing.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_preview' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Preview', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Show inline previews for PDFs, DOCX, and PPT files in media library.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'enable_inline_viewer' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Inline Document Viewer', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Embed responsive document viewers in posts and pages.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
						'search_settings' => array(
							'title'       => __( 'Search & Indexing', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Full-text search and document indexing settings.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_fulltext_search' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Full-Text Search', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Index document contents for searchability.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'index_file_types' => array(
									'type'         => 'checkbox',
									'label'        => __( 'Index File Types', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Select which document types to index for full-text search.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'options'      => array(
										'pdf'  => 'PDF',
										'docx' => 'DOCX/DOC',
										'pptx' => 'PPTX/PPT',
										'txt'  => 'TXT',
										'md'   => 'Markdown',
									),
									'default'      => array( 'pdf', 'docx', 'txt' ),
									'globalizable' => true,
								),
							),
						),
						'management_settings' => array(
							'title'       => __( 'Document Management', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Version control, collections, and organization features.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_version_control' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Version Control', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Track document versions and allow rollback.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'enable_collections' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Collections', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Group related documents into collections.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'enable_expiry' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Expiry', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Set expiration dates and auto-archive documents.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
							),
						),
						'security_settings' => array(
							'title'       => __( 'Security & Access', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Role-based access control and secure sharing.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_rbac' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Role-Based Access Control', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Restrict document access by user roles.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'enable_secure_links' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Secure Sharing Links', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Generate time-limited, password-protected download links.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
							),
						),
						'analytics_settings' => array(
							'title'       => __( 'Analytics & Tracking', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Track document usage and engagement.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_analytics' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Analytics', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Track document views, downloads, and engagement.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'track_downloads' => array(
									'type'         => 'toggle',
									'label'        => __( 'Track Downloads', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Show download counts and popularity metrics.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
						'advanced_settings' => array(
							'title'       => __( 'Advanced Features', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'description' => __( 'Conversion, collaboration, and AI-powered features.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
							'fields'      => array(
								'enable_conversion' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Conversion', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Convert between document formats (PDF, DOCX, etc.).', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'enable_templates' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Document Templates', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Provide pre-built document templates.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
								'enable_ai_summaries' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable AI Summaries', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'description'  => __( 'Generate AI-powered document summaries and key points.', TIMU_DOCUMENTS_TEXT_DOMAIN ),
									'default'      => 0,
									'globalizable' => true,
								),
							),
						),
					)
				);
			}

			/**
			 * Add admin menu items.
			 */
			public function add_admin_menu(): void {
				$this->add_admin_submenu( strtoupper( __( 'Documents', TIMU_DOCUMENTS_TEXT_DOMAIN ) ) );
			}

			/**
			 * Render settings page.
			 */
			public function render_settings_page(): void {
				$this->render_settings_page_base( strtoupper( __( 'Documents Support', TIMU_DOCUMENTS_TEXT_DOMAIN ) ) );
			}
		}
	}

	new TIMU_Documents_Support();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\timu_documents_init', 13 );

/**
 * Admin notice for missing Core.
 */
function timu_documents_missing_core_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Documents Support requires Core Support to be installed and active.', TIMU_DOCUMENTS_TEXT_DOMAIN )
	);
}

/**
 * Admin notice for missing Media Hub.
 */
function timu_documents_missing_hub_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Documents Support requires Media Support Hub to be installed and active.', TIMU_DOCUMENTS_TEXT_DOMAIN )
	);
}

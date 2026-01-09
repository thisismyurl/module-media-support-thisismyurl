<?php
/**
 * Video Support Module
 *
 * This module is loaded by the TIMU Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of the Media Hub.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_VIDEO_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\VideoSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TIMU_VIDEO_VERSION', '1.0.0' );
define( 'TIMU_VIDEO_FILE', __FILE__ );
define( 'TIMU_VIDEO_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIMU_VIDEO_URL', plugin_dir_url( __FILE__ ) );
define( 'TIMU_VIDEO_BASENAME', plugin_basename( __FILE__ ) );
define( 'TIMU_VIDEO_TEXT_DOMAIN', 'videos-support-thisismyurl' );
define( 'TIMU_VIDEO_MIN_PHP', '8.1.29' );
define( 'TIMU_VIDEO_MIN_WP', '6.4.0' );
define( 'TIMU_VIDEO_REQUIRES_MEDIA_HUB', 'media-support-thisismyurl' );

/**
 * Initialize Video Support.
 */
function timu_video_init(): void {
	// Verify Core is present.
	if ( ! class_exists( '\\TIMU\\Core\\Spoke\\TIMU_Spoke_Base' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_video_missing_core_notice' );
		return;
	}

	// Verify Media Hub is present.
	if ( ! defined( 'TIMU_MEDIA_VERSION' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\timu_video_missing_media_hub_notice' );
		return;
	}

	// Register with Core module registry (Spoke-level under media hub).
	do_action(
		'timu_register_module',
		array(
			'slug'         => 'videos-support-thisismyurl',
			'name'         => __( 'Video Support', TIMU_VIDEO_TEXT_DOMAIN ),
			'type'         => 'spoke',
			'suite'        => 'media',
			'parent'       => 'media-support-thisismyurl',
			'version'      => TIMU_VIDEO_VERSION,
			'description'  => __( 'Advanced video processing, editing, and playback enhancements.', TIMU_VIDEO_TEXT_DOMAIN ),
			'capabilities' => array(
				'video_editor',
				'thumbnail_generation',
				'video_chapters',
				'bulk_optimization',
				'smart_tagging',
				'video_collections',
				'caption_editor',
				'brand_overlay',
				'social_export',
				'video_analytics',
				'adaptive_streaming',
				'interactive_features',
			),
			'path'         => TIMU_VIDEO_PATH,
			'url'          => TIMU_VIDEO_URL,
			'basename'     => TIMU_VIDEO_BASENAME,
		)
	);

	// Register video_processing feature.
	if ( function_exists( '\TIMU\CoreSupport\register_timu_feature' ) ) {
		\TIMU\CoreSupport\register_timu_feature(
			'video_processing',
			array(
				'plugin'      => 'videos-support-thisismyurl',
				'name'        => __( 'Video Processing', TIMU_VIDEO_TEXT_DOMAIN ),
				'description' => __( 'Advanced video editing, optimization, and playback features', TIMU_VIDEO_TEXT_DOMAIN ),
				'version'     => TIMU_VIDEO_VERSION,
			)
		);
	}

	// Initialize the video support class.
	if ( ! class_exists( __NAMESPACE__ . '\\TIMU_Video_Support' ) ) {
		class TIMU_Video_Support extends \TIMU\Core\Spoke\TIMU_Spoke_Base {
			/**
			 * Constructor.
			 */
			public function __construct() {
				parent::__construct( 'videos-support-thisismyurl', TIMU_VIDEO_URL, 'timu_video_settings_group', 'dashicons-video-alt3', 'media-support-thisismyurl' );
				add_action( 'init', array( $this, 'setup_plugin' ), 20 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
			}

			/**
			 * Setup plugin.
			 */
			public function setup_plugin(): void {
				$this->is_licensed();
				$this->init_settings_generator( $this->get_settings_config() );
				$this->init_admin_features();
				$this->init_frontend_features();
			}

			/**
			 * Get settings configuration.
			 *
			 * @return array Settings configuration array.
			 */
			private function get_settings_config(): array {
				return array(
					'admin_features'    => array(
						'title'       => __( 'Admin-Side Video Enhancements', TIMU_VIDEO_TEXT_DOMAIN ),
						'description' => __( 'Tools and features for video management in the WordPress admin.', TIMU_VIDEO_TEXT_DOMAIN ),
						'fields'      => array(
							'video_editor'           => array(
								'type'         => 'toggle',
								'label'        => __( 'Drag-and-Drop Video Editor', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Enable trim, crop, and merge capabilities in Media Library.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'thumbnail_generation'   => array(
								'type'         => 'toggle',
								'label'        => __( 'Auto-Generate Thumbnails & Preview GIFs', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Automatically create thumbnail options and animated previews.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'video_chapters'         => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Chapters & Timestamps', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Add chapters in Media Library with clickable timestamps.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'bulk_optimization'      => array(
								'type'         => 'toggle',
								'label'        => __( 'Bulk Video Optimization', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Compress videos and generate multiple resolutions.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'smart_tagging'          => array(
								'type'         => 'toggle',
								'label'        => __( 'Smart Video Tagging', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Detect objects/scenes and suggest tags automatically.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'video_collections'      => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Collections & Playlists', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Group videos into playlists for campaigns or tutorials.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'caption_editor'         => array(
								'type'         => 'toggle',
								'label'        => __( 'Inline Caption & Subtitle Editor', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Upload or auto-generate captions with inline editing.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'brand_overlay'          => array(
								'type'         => 'toggle',
								'label'        => __( 'Brand Overlay & Watermark Tool', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Apply logos or text overlays for branding.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'social_export'          => array(
								'type'         => 'toggle',
								'label'        => __( 'Social Media Export Presets', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Export videos for Instagram Reel, TikTok, YouTube Shorts.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'video_analytics'        => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Analytics Dashboard', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Track views, engagement, and performance per video.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'video_replacement'      => array(
								'type'         => 'toggle',
								'label'        => __( 'Replace Video Without Breaking Links', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Safe replace feature with version history.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'scheduled_publishing'   => array(
								'type'         => 'toggle',
								'label'        => __( 'Scheduled Video Publishing', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Upload now, schedule for later.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'thumbnail_designer'     => array(
								'type'         => 'toggle',
								'label'        => __( 'Interactive Thumbnail Designer', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Add text, stickers, and filters to thumbnails.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'metadata_control'       => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Metadata Control', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Manage SEO fields, schema, and Open Graph tags.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'external_integrations' => array(
								'type'         => 'toggle',
								'label'        => __( 'External Integrations', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Integration with Canva & Adobe Express.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
						),
					),
					'frontend_features' => array(
						'title'       => __( 'End-User Video Experience', TIMU_VIDEO_TEXT_DOMAIN ),
						'description' => __( 'Enhanced video playback and interaction features for site visitors.', TIMU_VIDEO_TEXT_DOMAIN ),
						'fields'      => array(
							'adaptive_streaming'    => array(
								'type'         => 'toggle',
								'label'        => __( 'Adaptive Streaming (HLS/DASH)', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Serve videos in multiple resolutions for smooth playback.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'picture_in_picture'    => array(
								'type'         => 'toggle',
								'label'        => __( 'Picture-in-Picture Mode', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Let users keep watching while scrolling.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'interactive_hotspots'  => array(
								'type'         => 'toggle',
								'label'        => __( 'Interactive Video Hotspots', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Clickable areas for links or product info.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'chapter_navigation'    => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Chapters in Player', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Clickable chapter navigation for long-form content.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'social_sharing'        => array(
								'type'         => 'toggle',
								'label'        => __( 'Social Sharing from Video Player', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Share specific timestamps or clips to social platforms.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'lightbox_playback'     => array(
								'type'         => 'toggle',
								'label'        => __( 'Lightbox Video Playback', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Open videos in fullscreen modal.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
							'smart_autoplay'        => array(
								'type'         => 'toggle',
								'label'        => __( 'Autoplay with Smart Controls', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Autoplay muted on scroll with hover-to-unmute.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'video_reactions'       => array(
								'type'         => 'toggle',
								'label'        => __( 'Video Reaction & Comment Overlay', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Allow reactions or comments tied to timestamps.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'vr_360_support'        => array(
								'type'         => 'toggle',
								'label'        => __( '360° & VR Video Support', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Interactive panoramic videos for immersive experiences.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 0,
								'globalizable' => true,
							),
							'end_screen_ctas'       => array(
								'type'         => 'toggle',
								'label'        => __( 'End-Screen CTAs & Links', TIMU_VIDEO_TEXT_DOMAIN ),
								'description'  => __( 'Add clickable calls-to-action at video end.', TIMU_VIDEO_TEXT_DOMAIN ),
								'default'      => 1,
								'globalizable' => true,
							),
						),
					),
				);
			}

			/**
			 * Initialize admin features.
			 */
			private function init_admin_features(): void {
				// Hook into WordPress admin to add video-specific functionality.
				add_filter( 'attachment_fields_to_edit', array( $this, 'add_video_metadata_fields' ), 10, 2 );
				add_filter( 'attachment_fields_to_save', array( $this, 'save_video_metadata_fields' ), 10, 2 );

				// Add video-specific columns to media library.
				add_filter( 'manage_media_columns', array( $this, 'add_video_columns' ) );
				add_action( 'manage_media_custom_column', array( $this, 'render_video_columns' ), 10, 2 );

				// Register AJAX handlers for video processing.
				add_action( 'wp_ajax_timu_generate_video_thumbnail', array( $this, 'ajax_generate_thumbnail' ) );
				add_action( 'wp_ajax_timu_save_video_chapters', array( $this, 'ajax_save_chapters' ) );
				add_action( 'wp_ajax_timu_optimize_video', array( $this, 'ajax_optimize_video' ) );
				add_action( 'wp_ajax_timu_export_social_video', array( $this, 'ajax_export_social_video' ) );
			}

			/**
			 * Initialize frontend features.
			 */
			private function init_frontend_features(): void {
				// Add video player shortcode.
				add_shortcode( 'timu_video', array( $this, 'render_video_player_shortcode' ) );

				// Filter video embed output.
				add_filter( 'wp_video_shortcode', array( $this, 'filter_video_embed' ), 10, 5 );

				// Add video player customization hooks.
				add_action( 'wp_footer', array( $this, 'render_video_templates' ) );
			}

			/**
			 * Enqueue admin assets.
			 *
			 * @param string $hook Current admin page hook.
			 */
			public function enqueue_admin_assets( string $hook ): void {
				// Only load on media pages.
				if ( ! in_array( $hook, array( 'upload.php', 'post.php', 'post-new.php' ), true ) ) {
					return;
				}

				// Placeholder for admin scripts and styles.
				// In a full implementation, this would load video editor UI, thumbnail generator, etc.
			}

			/**
			 * Enqueue frontend assets.
			 */
			public function enqueue_frontend_assets(): void {
				// Placeholder for frontend video player scripts and styles.
				// In a full implementation, this would load HLS.js, video player UI, etc.
			}

			/**
			 * Add video metadata fields to attachment editor.
			 *
			 * @param array    $form_fields Array of attachment form fields.
			 * @param \WP_Post $post        The attachment post object.
			 * @return array Modified form fields.
			 */
			public function add_video_metadata_fields( array $form_fields, \WP_Post $post ): array {
				if ( ! wp_attachment_is( 'video', $post ) ) {
					return $form_fields;
				}

				// Add chapters field.
				if ( $this->get_setting( 'admin_features', 'video_chapters', 1 ) ) {
					$form_fields['timu_video_chapters'] = array(
						'label' => __( 'Video Chapters', TIMU_VIDEO_TEXT_DOMAIN ),
						'input' => 'textarea',
						'value' => get_post_meta( $post->ID, '_timu_video_chapters', true ),
						'helps' => __( 'Enter chapters in format: 00:00 - Intro, 01:30 - Main Content', TIMU_VIDEO_TEXT_DOMAIN ),
					);
				}

				// Add metadata fields.
				if ( $this->get_setting( 'admin_features', 'metadata_control', 1 ) ) {
					$form_fields['timu_video_description'] = array(
						'label' => __( 'SEO Description', TIMU_VIDEO_TEXT_DOMAIN ),
						'input' => 'textarea',
						'value' => get_post_meta( $post->ID, '_timu_video_description', true ),
					);
				}

				return $form_fields;
			}

			/**
			 * Save video metadata fields.
			 *
			 * @param array $post       Attachment post data.
			 * @param array $attachment Attachment fields from $_POST.
			 * @return array Post data.
			 */
			public function save_video_metadata_fields( array $post, array $attachment ): array {
				if ( isset( $attachment['timu_video_chapters'] ) ) {
					update_post_meta( $post['ID'], '_timu_video_chapters', sanitize_textarea_field( $attachment['timu_video_chapters'] ) );
				}

				if ( isset( $attachment['timu_video_description'] ) ) {
					update_post_meta( $post['ID'], '_timu_video_description', sanitize_textarea_field( $attachment['timu_video_description'] ) );
				}

				return $post;
			}

			/**
			 * Add video columns to media library.
			 *
			 * @param array $columns Existing columns.
			 * @return array Modified columns.
			 */
			public function add_video_columns( array $columns ): array {
				$columns['timu_video_duration'] = __( 'Duration', TIMU_VIDEO_TEXT_DOMAIN );
				$columns['timu_video_chapters']  = __( 'Chapters', TIMU_VIDEO_TEXT_DOMAIN );
				return $columns;
			}

			/**
			 * Render video columns in media library.
			 *
			 * @param string $column_name Name of the column.
			 * @param int    $post_id     Attachment ID.
			 */
			public function render_video_columns( string $column_name, int $post_id ): void {
				if ( ! wp_attachment_is( 'video', $post_id ) ) {
					return;
				}

				switch ( $column_name ) {
					case 'timu_video_duration':
						$metadata = wp_get_attachment_metadata( $post_id );
						if ( isset( $metadata['length_formatted'] ) ) {
							echo esc_html( $metadata['length_formatted'] );
						}
						break;

					case 'timu_video_chapters':
						$chapters = get_post_meta( $post_id, '_timu_video_chapters', true );
						if ( $chapters ) {
							echo '<span class="dashicons dashicons-list-view" title="' . esc_attr__( 'Has chapters', TIMU_VIDEO_TEXT_DOMAIN ) . '"></span>';
						} else {
							echo '—';
						}
						break;
				}
			}

			/**
			 * AJAX handler for thumbnail generation.
			 */
			public function ajax_generate_thumbnail(): void {
				check_ajax_referer( 'timu-video-nonce', 'nonce' );

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				$attachment_id = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;

				if ( ! $attachment_id || ! wp_attachment_is( 'video', $attachment_id ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid video attachment.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				// Placeholder for thumbnail generation logic.
				wp_send_json_success( array( 'message' => __( 'Thumbnail generation queued.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
			}

			/**
			 * AJAX handler for saving video chapters.
			 */
			public function ajax_save_chapters(): void {
				check_ajax_referer( 'timu-video-nonce', 'nonce' );

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				$attachment_id = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;
				$chapters      = isset( $_POST['chapters'] ) ? sanitize_textarea_field( wp_unslash( $_POST['chapters'] ) ) : '';

				if ( ! $attachment_id ) {
					wp_send_json_error( array( 'message' => __( 'Invalid attachment ID.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				update_post_meta( $attachment_id, '_timu_video_chapters', $chapters );
				wp_send_json_success( array( 'message' => __( 'Chapters saved successfully.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
			}

			/**
			 * AJAX handler for video optimization.
			 */
			public function ajax_optimize_video(): void {
				check_ajax_referer( 'timu-video-nonce', 'nonce' );

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				$attachment_id = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;

				if ( ! $attachment_id || ! wp_attachment_is( 'video', $attachment_id ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid video attachment.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				// Placeholder for video optimization logic.
				wp_send_json_success( array( 'message' => __( 'Video optimization queued.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
			}

			/**
			 * AJAX handler for social media export.
			 */
			public function ajax_export_social_video(): void {
				check_ajax_referer( 'timu-video-nonce', 'nonce' );

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				$attachment_id = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;
				$platform      = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : '';

				if ( ! $attachment_id || ! wp_attachment_is( 'video', $attachment_id ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid video attachment.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				if ( ! in_array( $platform, array( 'instagram', 'tiktok', 'youtube_shorts' ), true ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid platform.', TIMU_VIDEO_TEXT_DOMAIN ) ) );
				}

				// Placeholder for social media export logic.
				wp_send_json_success(
					array(
						'message'  => __( 'Social media export queued.', TIMU_VIDEO_TEXT_DOMAIN ),
						'platform' => $platform,
					)
				);
			}

			/**
			 * Render video player shortcode.
			 *
			 * @param array $atts Shortcode attributes.
			 * @return string Video player HTML.
			 */
			public function render_video_player_shortcode( array $atts ): string {
				$atts = shortcode_atts(
					array(
						'id'       => 0,
						'chapters' => 'true',
						'pip'      => 'true',
						'sharing'  => 'true',
					),
					$atts,
					'timu_video'
				);

				$attachment_id = intval( $atts['id'] );
				if ( ! $attachment_id || ! wp_attachment_is( 'video', $attachment_id ) ) {
					return '';
				}

				$video_url = wp_get_attachment_url( $attachment_id );
				if ( ! $video_url ) {
					return '';
				}

				$chapters         = get_post_meta( $attachment_id, '_timu_video_chapters', true );
				$pip_attribute    = ( 'true' === $atts['pip'] ) ? '' : 'disablePictureInPicture';

				ob_start();
				?>
				<div class="timu-video-player" data-video-id="<?php echo esc_attr( $attachment_id ); ?>">
					<video controls <?php echo $pip_attribute; ?>>
						<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( get_post_mime_type( $attachment_id ) ); ?>">
						<?php esc_html_e( 'Your browser does not support the video tag.', TIMU_VIDEO_TEXT_DOMAIN ); ?>
					</video>
					<?php if ( 'true' === $atts['chapters'] && $chapters ) : ?>
						<div class="timu-video-chapters">
							<?php echo wp_kses_post( wpautop( $chapters ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( 'true' === $atts['sharing'] ) : ?>
						<div class="timu-video-sharing">
							<button class="timu-share-button" data-platform="twitter"><?php esc_html_e( 'Share on Twitter', TIMU_VIDEO_TEXT_DOMAIN ); ?></button>
							<button class="timu-share-button" data-platform="facebook"><?php esc_html_e( 'Share on Facebook', TIMU_VIDEO_TEXT_DOMAIN ); ?></button>
						</div>
					<?php endif; ?>
				</div>
				<?php
				$output = ob_get_clean();
				return $output ? $output : '';
			}

			/**
			 * Filter video embed output.
			 *
			 * @param string $output  Video shortcode HTML output.
			 * @param array  $atts    Array of video shortcode attributes.
			 * @param string $video   Video file URL.
			 * @param int    $post_id Post ID.
			 * @param string $library Media library used for the video shortcode.
			 * @return string Modified video HTML.
			 */
			public function filter_video_embed( string $output, array $atts, string $video, int $post_id, string $library ): string {
				// Add custom data attributes for enhanced video features.
				if ( $this->get_setting( 'frontend_features', 'adaptive_streaming', 1 ) ) {
					$output = str_replace( '<video', '<video data-adaptive-streaming="true"', $output );
				}

				if ( $this->get_setting( 'frontend_features', 'picture_in_picture', 1 ) ) {
					$output = str_replace( '<video', '<video data-pip-enabled="true"', $output );
				}

				return $output;
			}

			/**
			 * Render video templates in footer.
			 */
			public function render_video_templates(): void {
				if ( ! $this->get_setting( 'frontend_features', 'lightbox_playback', 1 ) ) {
					return;
				}

				?>
				<div id="timu-video-lightbox" class="timu-video-lightbox" style="display:none;">
					<div class="timu-lightbox-overlay"></div>
					<div class="timu-lightbox-content">
						<button class="timu-lightbox-close">&times;</button>
						<div class="timu-lightbox-video-container"></div>
					</div>
				</div>
				<?php
			}

			/**
			 * Get a setting value.
			 *
			 * @param string $section Section name.
			 * @param string $key     Setting key.
			 * @param mixed  $default Default value.
			 * @return mixed Setting value.
			 */
			private function get_setting( string $section, string $key, $default = null ) {
				$options = get_option( 'timu_video_settings_group', array() );
				if ( ! isset( $options[ $section ] ) || ! is_array( $options[ $section ] ) ) {
					return $default;
				}
				return $options[ $section ][ $key ] ?? $default;
			}

			/**
			 * Render settings page.
			 */
			public function render_settings_page(): void {
				$this->render_settings_page_base( strtoupper( __( 'Video Support', TIMU_VIDEO_TEXT_DOMAIN ) ) );
			}
		}
	}

	new TIMU_Video_Support();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\timu_video_init', 13 );

/**
 * Admin notice for missing Core.
 */
function timu_video_missing_core_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Video Support requires Core Support to be installed and active.', TIMU_VIDEO_TEXT_DOMAIN )
	);
}

/**
 * Admin notice for missing Media Hub.
 */
function timu_video_missing_media_hub_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Video Support requires Media Support (Media Hub) to be installed and active.', TIMU_VIDEO_TEXT_DOMAIN )
	);
}

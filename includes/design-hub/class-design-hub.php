<?php
/**
 * Design Hub Class
 *
 * Unified hub for multiple design app integrations (Canva, Crello, Adobe Express, Figma).
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport\DesignHub;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Design Hub Class
 */
class Design_Hub {
	/**
	 * Instance of this class
	 *
	 * @var Design_Hub|null
	 */
	private static ?Design_Hub $instance = null;

	/**
	 * Registered design app connectors
	 *
	 * @var array
	 */
	private array $connectors = array();

	/**
	 * Get singleton instance
	 *
	 * @return Design_Hub
	 */
	public static function get_instance(): Design_Hub {
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
		$this->register_default_connectors();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_design_hub_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register default design app connectors
	 */
	private function register_default_connectors(): void {
		// Register Canva.
		$this->register_connector(
			'canva',
			array(
				'name'        => __( 'Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'description' => __( 'Create and edit designs with Canva', TIMU_MEDIA_TEXT_DOMAIN ),
				'icon'        => 'dashicons-art',
				'enabled'     => get_option( 'timu_design_hub_canva_enabled', true ),
			)
		);

		// Register Crello.
		$this->register_connector(
			'crello',
			array(
				'name'        => __( 'Crello (VistaCreate)', TIMU_MEDIA_TEXT_DOMAIN ),
				'description' => __( 'Design with Crello/VistaCreate', TIMU_MEDIA_TEXT_DOMAIN ),
				'icon'        => 'dashicons-format-image',
				'enabled'     => get_option( 'timu_design_hub_crello_enabled', false ),
			)
		);

		// Register Adobe Express.
		$this->register_connector(
			'adobe_express',
			array(
				'name'        => __( 'Adobe Express', TIMU_MEDIA_TEXT_DOMAIN ),
				'description' => __( 'Create with Adobe Express', TIMU_MEDIA_TEXT_DOMAIN ),
				'icon'        => 'dashicons-admin-customizer',
				'enabled'     => get_option( 'timu_design_hub_adobe_express_enabled', false ),
			)
		);

		// Register Figma.
		$this->register_connector(
			'figma',
			array(
				'name'        => __( 'Figma', TIMU_MEDIA_TEXT_DOMAIN ),
				'description' => __( 'Import designs from Figma', TIMU_MEDIA_TEXT_DOMAIN ),
				'icon'        => 'dashicons-layout',
				'enabled'     => get_option( 'timu_design_hub_figma_enabled', false ),
			)
		);
	}

	/**
	 * Register a design app connector
	 *
	 * @param string $id Connector ID.
	 * @param array  $args Connector arguments.
	 */
	public function register_connector( string $id, array $args ): void {
		$this->connectors[ $id ] = wp_parse_args(
			$args,
			array(
				'name'        => '',
				'description' => '',
				'icon'        => 'dashicons-admin-generic',
				'enabled'     => false,
				'callback'    => null,
			)
		);
	}

	/**
	 * Get all registered connectors
	 *
	 * @return array
	 */
	public function get_connectors(): array {
		return $this->connectors;
	}

	/**
	 * Get enabled connectors
	 *
	 * @return array
	 */
	public function get_enabled_connectors(): array {
		return array_filter(
			$this->connectors,
			function ( $connector ) {
				return $connector['enabled'];
			}
		);
	}

	/**
	 * Add Design Hub menu
	 */
	public function add_design_hub_menu(): void {
		add_submenu_page(
			'upload.php',
			__( 'Design Hub', TIMU_MEDIA_TEXT_DOMAIN ),
			__( 'Design Hub', TIMU_MEDIA_TEXT_DOMAIN ),
			'upload_files',
			'timu-design-hub',
			array( $this, 'render_design_hub_page' )
		);
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( string $hook ): void {
		if ( 'media_page_timu-design-hub' !== $hook && 'upload.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'timu-design-hub',
			TIMU_MEDIA_URL . 'assets/css/design-hub.css',
			array(),
			TIMU_MEDIA_VERSION
		);

		wp_enqueue_script(
			'timu-design-hub',
			TIMU_MEDIA_URL . 'assets/js/design-hub.js',
			array( 'jquery' ),
			TIMU_MEDIA_VERSION,
			true
		);

		wp_localize_script(
			'timu-design-hub',
			'timuDesignHub',
			array(
				'connectors' => $this->get_enabled_connectors(),
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'timu-design-hub-nonce' ),
			)
		);
	}

	/**
	 * Render Design Hub page
	 */
	public function render_design_hub_page(): void {
		$enabled_connectors = $this->get_enabled_connectors();
		?>
		<div class="wrap timu-design-hub">
			<h1><?php esc_html_e( 'Design Hub', TIMU_MEDIA_TEXT_DOMAIN ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Create and manage designs from multiple design platforms.', TIMU_MEDIA_TEXT_DOMAIN ); ?>
			</p>

			<div class="timu-design-hub-connectors">
				<?php if ( empty( $enabled_connectors ) ) : ?>
					<div class="notice notice-info">
						<p>
							<?php
							printf(
								/* translators: %s: settings page URL */
								esc_html__( 'No design apps are currently enabled. %s to get started.', TIMU_MEDIA_TEXT_DOMAIN ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=timu-core-support&tab=media-support-thisismyurl' ) ) . '">' . esc_html__( 'Visit settings', TIMU_MEDIA_TEXT_DOMAIN ) . '</a>'
							);
							?>
						</p>
					</div>
				<?php else : ?>
					<div class="timu-connector-grid">
						<?php foreach ( $enabled_connectors as $id => $connector ) : ?>
							<div class="timu-connector-card" data-connector="<?php echo esc_attr( $id ); ?>">
								<div class="timu-connector-icon">
									<span class="dashicons <?php echo esc_attr( $connector['icon'] ); ?>"></span>
								</div>
								<h3><?php echo esc_html( $connector['name'] ); ?></h3>
								<p><?php echo esc_html( $connector['description'] ); ?></p>
								<button type="button" class="button button-primary timu-connector-launch" data-connector="<?php echo esc_attr( $id ); ?>">
									<?php esc_html_e( 'Launch', TIMU_MEDIA_TEXT_DOMAIN ); ?>
								</button>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="timu-design-hub-recent">
				<h2><?php esc_html_e( 'Recent Designs', TIMU_MEDIA_TEXT_DOMAIN ); ?></h2>
				<div id="timu-recent-designs-list">
					<?php $this->render_recent_designs(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render recent designs
	 */
	private function render_recent_designs(): void {
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 12,
				'post_mime_type' => 'image',
				'meta_query'     => array(
					array(
						'key'     => '_canva_design_id',
						'compare' => 'EXISTS',
					),
				),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( $query->have_posts() ) {
			echo '<div class="timu-designs-grid">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$attachment_id = get_the_ID();
				$design_id = get_post_meta( $attachment_id, '_canva_design_id', true );
				$thumbnail = wp_get_attachment_image_url( $attachment_id, 'medium' );
				?>
				<div class="timu-design-item">
					<div class="timu-design-thumbnail">
						<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
					</div>
					<div class="timu-design-info">
						<h4><?php the_title(); ?></h4>
						<div class="timu-design-actions">
							<button type="button" class="button button-small timu-edit-design" data-attachment-id="<?php echo esc_attr( $attachment_id ); ?>">
								<?php esc_html_e( 'Edit', TIMU_MEDIA_TEXT_DOMAIN ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p class="description">' . esc_html__( 'No designs yet. Create your first design using one of the apps above.', TIMU_MEDIA_TEXT_DOMAIN ) . '</p>';
		}
	}
}

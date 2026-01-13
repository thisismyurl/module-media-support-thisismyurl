<?php
/**
 * TIMU Suite Module Stub (Not a WordPress plugin)
 *
 * This file exists in the repository for development, but should not be
 * recognized by WordPress as a plugin. The real module is loaded by the
 * TIMU Core Support via its Module Registry.
 *
 * @package TIMU_MEDIA_SUPPORT
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Return plugin version.
 */
function timu_media_support_version(): string
{
    return '1.260110.1519';
}

/**
 * Determine if settings should be managed at network level.
 */
function timu_media_support_is_network_mode(): bool
{
    return function_exists('is_multisite') && is_multisite() && is_network_admin();
}

/**
 * Activation: seed default options.
 */
function timu_media_support_activate(): void
{
    $defaults = [
        'vault_enabled' => true,
        'exif_scrub'    => true,
        'webp_enabled'  => false,
        'debug_mode'    => false,
    ];

    if (function_exists('is_multisite') && is_multisite()) {
        add_site_option('timu_media_support', $defaults);
    } else {
        add_option('timu_media_support', $defaults);
    }
}

/**
 * Deactivation: keep settings for future use.
 */
function timu_media_support_deactivate(): void
{
    // Intentionally keep options; no destructive action.
}

register_activation_hook(__FILE__, 'timu_media_support_activate');
register_deactivation_hook(__FILE__, 'timu_media_support_deactivate');

/**
 * Register admin menus (site + network).
 */
function timu_media_support_register_menu(): void
{
    $cap       = 'manage_options';
    $pageTitle = __('Media Support', 'module-media-support-thisismyurl');
    $menuTitle = __('Media Support', 'module-media-support-thisismyurl');
    $slug      = 'timu-media-support';

    add_menu_page(
        $pageTitle,
        $menuTitle,
        $cap,
        $slug,
        'timu_media_support_render_settings_page',
        'dashicons-admin-media',
        80
    );

    add_submenu_page(
        $slug,
        __('Settings', 'module-media-support-thisismyurl'),
        __('Settings', 'module-media-support-thisismyurl'),
        $cap,
        $slug,
        'timu_media_support_render_settings_page'
    );

    // Convenience link to Support → Help tab. Safe redirect with capability check.
    add_submenu_page(
        $slug,
        __('Get Help', 'module-media-support-thisismyurl'),
        __('Get Help', 'module-media-support-thisismyurl'),
        $cap,
        'timu-media-support-help',
        'timu_media_support_open_help'
    );
}

add_action('admin_menu', 'timu_media_support_register_menu');
add_action('network_admin_menu', 'timu_media_support_register_menu');

/**
 * Safe redirect to Support plugin Help tab.
 */
function timu_media_support_open_help(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'module-media-support-thisismyurl'));
    }

    // Note: Adjust the page slug/params once Support plugin exposes canonical Help tab URL.
    $url = admin_url('admin.php?page=timu-support&tab=help');
    wp_safe_redirect($url);
    exit;
}

/**
 * Register settings and fields.
 */
function timu_media_support_register_settings(): void
{
    $key = 'timu_media_support';

    register_setting(
        $key,
        $key,
        [
            'type'              => 'array',
            'sanitize_callback' => 'timu_media_support_sanitize_options',
            'default'           => [
                'vault_enabled' => true,
                'exif_scrub'    => true,
                'webp_enabled'  => false,
                'debug_mode'    => false,
            ],
        ]
    );

    add_settings_section(
        'timu_media_main',
        __('Core Settings', 'module-media-support-thisismyurl'),
        function (): void {
            echo '<p>' . esc_html__('Configure core media handling features.', 'module-media-support-thisismyurl') . '</p>';
        },
        'timu-media-support'
    );

    add_settings_field(
        'vault_enabled',
        __('Save Originals to Vault', 'module-media-support-thisismyurl'),
        function (): void {
            $opts = timu_media_support_get_options();
            echo '<label><input type="checkbox" name="timu_media_support[vault_enabled]" value="1"' . checked(true, !empty($opts['vault_enabled']), false) . '> ' . esc_html__('Enable Vault originals', 'module-media-support-thisismyurl') . '</label>';
        },
        'timu-media-support',
        'timu_media_main'
    );

    add_settings_field(
        'exif_scrub',
        __('Surgical Scrubbing (EXIF)', 'module-media-support-thisismyurl'),
        function (): void {
            $opts = timu_media_support_get_options();
            echo '<label><input type="checkbox" name="timu_media_support[exif_scrub]" value="1"' . checked(true, !empty($opts['exif_scrub']), false) . '> ' . esc_html__('Strip privacy EXIF (GPS)', 'module-media-support-thisismyurl') . '</label>';
        },
        'timu-media-support',
        'timu_media_main'
    );

    add_settings_field(
        'webp_enabled',
        __('WebP Conversion', 'module-media-support-thisismyurl'),
        function (): void {
            $opts = timu_media_support_get_options();
            echo '<label><input type="checkbox" name="timu_media_support[webp_enabled]" value="1"' . checked(true, !empty($opts['webp_enabled']), false) . '> ' . esc_html__('Generate WebP when supported', 'module-media-support-thisismyurl') . '</label>';
        },
        'timu-media-support',
        'timu_media_main'
    );

    add_settings_field(
        'debug_mode',
        __('Debug Mode', 'module-media-support-thisismyurl'),
        function (): void {
            $opts = timu_media_support_get_options();
            echo '<label><input type="checkbox" name="timu_media_support[debug_mode]" value="1"' . checked(true, !empty($opts['debug_mode']), false) . '> ' . esc_html__('Enable verbose logging', 'module-media-support-thisismyurl') . '</label>';
        },
        'timu-media-support',
        'timu_media_main'
    );
}

add_action('admin_init', 'timu_media_support_register_settings');

/**
 * Sanitize settings array.
 *
 * @param array|string $input Raw input
 * @return array
 */
function timu_media_support_sanitize_options($input): array
{
    $in = is_array($input) ? $input : [];
    return [
        'vault_enabled' => !empty($in['vault_enabled']),
        'exif_scrub'    => !empty($in['exif_scrub']),
        'webp_enabled'  => !empty($in['webp_enabled']),
        'debug_mode'    => !empty($in['debug_mode']),
    ];
}

/**
 * Fetch merged settings with defaults.
 */
function timu_media_support_get_options(): array
{
    $key = 'timu_media_support';
    $opts = [];

    if (function_exists('is_multisite') && is_multisite() && is_network_admin()) {
        $opts = get_site_option($key, []);
    } else {
        $opts = get_option($key, []);
    }

    if (!is_array($opts)) {
        $opts = [];
    }

    return wp_parse_args(
        $opts,
        [
            'vault_enabled' => true,
            'exif_scrub'    => true,
            'webp_enabled'  => false,
            'debug_mode'    => false,
        ]
    );
}

/**
 * Render admin settings page.
 */
function timu_media_support_render_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'plugin-media-support-thisismyurl'));
    }

    echo '<div class="wrap" role="main">';
    echo '<h1>' . esc_html__('Media Support Settings', 'plugin-media-support-thisismyurl') . '</h1>';
    echo '<form action="' . esc_url(admin_url('options.php')) . '" method="post">';
    settings_fields('timu_media_support');
    do_settings_sections('timu-media-support');
    submit_button(__('Save Changes', 'plugin-media-support-thisismyurl'));
    echo '</form>';
    echo '</div>';
}

/* @changelog
 - 2026-01-10: Initial bootstrap admin settings with multisite awareness and Help link.
*/

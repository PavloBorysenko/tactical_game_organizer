<?php
/**
 * Plugin Name: Tactical Game Organizer
 * Plugin URI: https://your-site.com/tactical-game-organizer
 * Description: Plugin for organizing airsoft events and managing teams
 * Version: 1.0.0
 * Author: NaGora
 * Author URI: https://your-site.com
 * Text Domain: tactical-game-organizer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Required WordPress functions
require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Enable error reporting for debugging
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', false);
}

// Plugin constants
define('TGO_VERSION', '1.0.0');
define('TGO_PLUGIN_FILE', __FILE__);
define('TGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    error_log('Tactical Game Organizer: Composer autoloader not found');
}

use TacticalGameOrganizer\PostTypes\Event;
use TacticalGameOrganizer\PostTypes\Field;
use TacticalGameOrganizer\Roles\PlayerRoles;
use TacticalGameOrganizer\Users\UserFields;
use TacticalGameOrganizer\Users\EventRegistration;
use function add_action;
use function register_activation_hook;
use function register_deactivation_hook;
use function wp_enqueue_style;
use function wp_enqueue_script;
use function plugins_url;
use function plugin_dir_path;
use function plugin_dir_url;
use function wp_localize_script;
use function esc_html__;
use function update_option;
use function get_option;
use function delete_option;
use function is_admin;
use function flush_rewrite_rules;
use function error_log;

/**
 * Initialize plugin
 */
function tgo_init(): void {
    try {
        // Initialize roles
        $roles = new PlayerRoles();
        $roles->init();
        error_log('Tactical Game Organizer: Roles initialized');

        // Initialize user fields
        $userFields = new UserFields();
        $userFields->init();
        error_log('Tactical Game Organizer: User fields initialized');

        // Initialize post types
        $event = new Event();
        $field = new Field();
        error_log('Tactical Game Organizer: Post types initialized');

        // Initialize event registration
        $eventRegistration = new EventRegistration();
        $eventRegistration->init();
        error_log('Tactical Game Organizer: Event registration initialized');

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', 'tgo_enqueue_scripts');
        add_action('admin_enqueue_scripts', 'tgo_enqueue_scripts');
    } catch (\Exception $e) {
        error_log('Tactical Game Organizer Error: ' . $e->getMessage());
    }
}
add_action('plugins_loaded', 'tgo_init');

/**
 * Enqueue scripts and styles
 */
function tgo_enqueue_scripts(): void {
    global $post;

    // Styles
    wp_enqueue_style(
        'tgo-event-registration',
        plugins_url('assets/css/event-registration.css', __FILE__),
        [],
        '1.0.0'
    );

    // Scripts
    wp_enqueue_script('wp-api-fetch');
    wp_enqueue_script(
        'tgo-event-registration',
        plugins_url('assets/js/event-registration.js', __FILE__),
        ['jquery', 'wp-api-fetch'],
        '1.0.0',
        true
    );

    wp_localize_script('tgo-event-registration', 'tgo_rest', [
        'no_participants' => esc_html__('No participants yet.', 'tactical-game-organizer'),
        'participants_title' => esc_html__('Participants', 'tactical-game-organizer'),
        'error_message' => esc_html__('An error occurred. Please try again.', 'tactical-game-organizer'),
        'event_full_message' => esc_html__('This event is full. No more registrations are accepted.', 'tactical-game-organizer'),
        'event_full_inline' => esc_html__('Unfortunately, there are no more spots available for this game.', 'tactical-game-organizer'),
        'select_role' => esc_html__('Select your role', 'tactical-game-organizer')
    ]);

    // Admin styles
    if (is_admin()) {
        wp_enqueue_style(
            'tgo-admin-style',
            plugins_url('assets/css/admin.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    // Frontend styles for event
    if ($post && $post->post_type === Event::POST_TYPE) {
        wp_enqueue_style(
            'tgo-event-style',
            plugins_url('assets/css/event.css', __FILE__),
            [],
            '1.0.0'
        );
        wp_enqueue_style('dashicons');
    }
}

/**
 * Plugin activation
 */
function tgo_activate(): void {
    try {
        // Initialize and register roles
        $roles = new PlayerRoles();
        $roles->registerRoles();
        error_log('Tactical Game Organizer: Roles registered on activation');

        // Set flag to flush rewrite rules on next admin load
        update_option('tgo_flush_rewrite_rules', true);
        error_log('Tactical Game Organizer: Rewrite rules flag set');
    } catch (\Exception $e) {
        error_log('Tactical Game Organizer Activation Error: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'tgo_activate');

/**
 * Plugin deactivation
 */
function tgo_deactivate(): void {
    try {
        // Remove custom roles
        PlayerRoles::removeRoles();
        error_log('Tactical Game Organizer: Roles removed on deactivation');

        // Set flag to flush rewrite rules on next admin load
        update_option('tgo_flush_rewrite_rules', true);
        error_log('Tactical Game Organizer: Rewrite rules flag set');
    } catch (\Exception $e) {
        error_log('Tactical Game Organizer Deactivation Error: ' . $e->getMessage());
    }
}
register_deactivation_hook(__FILE__, 'tgo_deactivate');

/**
 * Flush rewrite rules if the flag is set and we're in admin
 */
function tgo_flush_rewrite_rules(): void {
    if (is_admin() && get_option('tgo_flush_rewrite_rules')) {
        flush_rewrite_rules();
        delete_option('tgo_flush_rewrite_rules');
        error_log('Tactical Game Organizer: Rewrite rules flushed');
    }
}
add_action('admin_init', 'tgo_flush_rewrite_rules'); 
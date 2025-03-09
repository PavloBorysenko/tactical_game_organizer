<?php
/**
 * Plugin Name: Tactical Game Organizer
 * Plugin URI: https://example.com/tactical-game-organizer
 * Description: WordPress plugin for organizing airsoft events
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: tactical-game-organizer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('TGO_VERSION', '1.0.0');
define('TGO_PLUGIN_FILE', __FILE__);
define('TGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Initialize plugin
add_action('plugins_loaded', function() {
    \TacticalGameOrganizer\Core\Plugin::getInstance()->init();
}); 
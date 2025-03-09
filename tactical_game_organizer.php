<?php
/**
 * Plugin Name: Tactical Game Organizer
 * Plugin URI: 
 * Description: Plugin for organizing airsoft events with participant registration and Telegram integration
 * Version: 1.0.0
 * Author: NaGora <pablodevelophelp@gmail.com>
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tactical-game-organizer
 * Domain Path: /languages
 *
 * @package TacticalGameOrganizer
 * @category WordPress
 * @author NaGora <pablodevelophelp@gmail.com>
 * @license GPL-2.0-or-later
 * @link https://example.com/tactical-game-organizer
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('TGO_VERSION', '1.0.0');
define('TGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(TGO_PLUGIN_DIR . 'vendor/autoload.php')) {
    require TGO_PLUGIN_DIR . 'vendor/autoload.php';
}

use TacticalGameOrganizer\PostTypes\Event;
use TacticalGameOrganizer\PostTypes\Field;
use TacticalGameOrganizer\Meta\EventMeta;

/**
 * Initialize plugin classes
 *
 * @return void
 */
function tgo_init(): void {
    new Event();
    new Field();
    new EventMeta();
}
add_action('plugins_loaded', 'tgo_init');

/**
 * Plugin activation hook
 * 
 * This function is called when the plugin is activated.
 * It handles any necessary setup tasks such as:
 * - Flushing rewrite rules for custom post types
 * - Setting up default options
 * - Creating necessary database tables
 *
 * @return void
 */
function tgo_activate(): void {
    // Flush rewrite rules to handle custom post types
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'tgo_activate');

/**
 * Plugin deactivation hook
 *
 * @return void
 */
function tgo_deactivate(): void {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'tgo_deactivate'); 
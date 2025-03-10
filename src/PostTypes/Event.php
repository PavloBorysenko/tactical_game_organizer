<?php

declare(strict_types=1);

namespace TacticalGameOrganizer\PostTypes;

use TacticalGameOrganizer\Core\Template;
use TacticalGameOrganizer\Roles\PlayerRoles;
use WP_Post;

// WordPress Core Functions
use function add_action;
use function add_filter;
use function add_meta_box;
use function current_user_can;
use function esc_html__;
use function esc_html_x;
use function file_exists;
use function get_post_meta;
use function get_posts;
use function is_singular;
use function plugin_dir_path;
use function register_post_type;
use function sanitize_text_field;
use function update_post_meta;
use function wp_nonce_field;
use function wp_verify_nonce;

// WordPress Constants
use const DOING_AUTOSAVE;

/**
 * Class Event
 * 
 * Handles the registration and configuration of the Event custom post type
 * 
 * @package TacticalGameOrganizer\PostTypes
 */
class Event extends Template {
    /**
     * Post type name
     *
     * @var string
     */
    public const POST_TYPE = 'event';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', [$this, 'registerPostType']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'saveMetaBoxes']);
        add_filter('single_template', [$this, 'loadSingleTemplate']);
    }

    /**
     * Register the custom post type
     *
     * @return void
     */
    public function registerPostType(): void {
        $labels = [
            'name'                  => esc_html_x('Events', 'Post type general name', 'tactical-game-organizer'),
            'singular_name'         => esc_html_x('Event', 'Post type singular name', 'tactical-game-organizer'),
            'menu_name'            => esc_html_x('Events', 'Admin Menu text', 'tactical-game-organizer'),
            'name_admin_bar'        => esc_html_x('Event', 'Add New on Toolbar', 'tactical-game-organizer'),
            'add_new'              => esc_html__('Add New', 'tactical-game-organizer'),
            'add_new_item'         => esc_html__('Add New Event', 'tactical-game-organizer'),
            'new_item'             => esc_html__('New Event', 'tactical-game-organizer'),
            'edit_item'            => esc_html__('Edit Event', 'tactical-game-organizer'),
            'view_item'            => esc_html__('View Event', 'tactical-game-organizer'),
            'all_items'            => esc_html__('All Events', 'tactical-game-organizer'),
            'search_items'         => esc_html__('Search Events', 'tactical-game-organizer'),
            'not_found'            => esc_html__('No events found.', 'tactical-game-organizer'),
            'not_found_in_trash'   => esc_html__('No events found in Trash.', 'tactical-game-organizer'),
            'featured_image'       => esc_html__('Event Cover Image', 'tactical-game-organizer'),
            'set_featured_image'   => esc_html__('Set cover image', 'tactical-game-organizer'),
            'remove_featured_image' => esc_html__('Remove cover image', 'tactical-game-organizer'),
            'use_featured_image'   => esc_html__('Use as cover image', 'tactical-game-organizer'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'           => ['slug' => 'events'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'         => 'dashicons-calendar-alt',
            'supports'          => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'      => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Add meta boxes for event details
     *
     * @return void
     */
    public function addMetaBoxes(): void {
        add_meta_box(
            'event_details',
            esc_html__('Event Details', 'tactical-game-organizer'),
            [$this, 'renderEventDetailsMetaBox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
        add_meta_box(
            'tgo_event_roles',
            esc_html__('Allowed Roles', 'tactical-game-organizer'),
            [$this, 'renderRolesMetaBox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render event details meta box
     *
     * @param WP_Post $post Post object
     * @return void
     */
    public function renderEventDetailsMetaBox(WP_Post $post): void {
        wp_nonce_field('event_details', 'event_details_nonce');

        $data = [
            'event_date' => get_post_meta($post->ID, 'tgo_event_date', true),
            'event_field' => get_post_meta($post->ID, 'tgo_event_field', true),
            'max_participants' => get_post_meta($post->ID, 'tgo_event_max_participants', true),
            'fields' => get_posts([
                'post_type' => Field::POST_TYPE,
                'posts_per_page' => -1,
            ]),
        ];

        $this->renderTemplate('meta-boxes/event-meta.php', $data);
    }

    /**
     * Render roles meta box
     *
     * @param WP_Post $post Post object
     * @return void
     */
    public function renderRolesMetaBox(WP_Post $post): void {
        wp_nonce_field('tgo_event_roles', 'tgo_event_roles_nonce');
        
        $data = [
            'allowedRoles' => PlayerRoles::getAllowedRolesForEvent($post->ID),
            'allRoles' => PlayerRoles::getAllRoles(),
        ];

        $this->renderTemplate('meta-boxes/event-roles.php', $data);
    }

    /**
     * Save meta box data
     *
     * @param int $post_id Post ID
     * @return void
     */
    public function saveMetaBoxes(int $post_id): void {
        if (!isset($_POST['event_details_nonce']) || 
            !wp_verify_nonce($_POST['event_details_nonce'], 'event_details')) {
            return;
        }

        if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Combine date and time into a single datetime string
        if (isset($_POST['tgo_event_date']) && isset($_POST['tgo_event_time'])) {
            $date = sanitize_text_field($_POST['tgo_event_date']);
            $time = sanitize_text_field($_POST['tgo_event_time']);
            $datetime = sprintf('%s %s:00', $date, $time);

            update_post_meta(
                $post_id, 
                'tgo_event_date', 
                $datetime
            );
        }

        if (isset($_POST['tgo_event_field'])) {
            update_post_meta(
                $post_id, 
                'tgo_event_field', 
                intval($_POST['tgo_event_field'])
            );
        }

        if (isset($_POST['tgo_event_max_participants'])) {
            update_post_meta(
                $post_id, 
                'tgo_event_max_participants', 
                intval($_POST['tgo_event_max_participants'])
            );
        }

        // Save allowed roles
        if (isset($_POST['tgo_event_roles_nonce']) && wp_verify_nonce($_POST['tgo_event_roles_nonce'], 'tgo_event_roles')) {
            $allowedRoles = isset($_POST['tgo_allowed_roles']) ? (array) $_POST['tgo_allowed_roles'] : [];
            PlayerRoles::saveAllowedRolesForEvent($post_id, $allowedRoles);
        }
    }

    /**
     * Load single event template
     *
     * @param string $template Template path
     * @return string
     */
    public function loadSingleTemplate(string $template): string {
        if (is_singular(self::POST_TYPE)) {
            $custom_template = $this->getTemplatePath('single-event.php');
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Get template path
     *
     * @param string $template Template name
     * @return string
     */
    protected function getTemplatePath(string $template): string {
        return plugin_dir_path(dirname(__DIR__)) . 'src/templates/' . $template;
    }
} 
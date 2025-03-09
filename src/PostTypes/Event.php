<?php

namespace TacticalGameOrganizer\PostTypes;

use function add_action;
use function register_post_type;
use function esc_html__;
use function esc_html_x;
use function add_meta_box;
use function get_post_meta;
use function wp_nonce_field;
use function sanitize_text_field;
use function update_post_meta;
use function absint;

/**
 * Class Event
 * 
 * Handles the registration and configuration of the Event custom post type
 * 
 * @package TacticalGameOrganizer\PostTypes
 */
class Event {
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
    }

    /**
     * Render event details meta box
     *
     * @param \WP_Post $post Post object
     */
    public function renderEventDetailsMetaBox(\WP_Post $post): void {
        $event_date = get_post_meta($post->ID, 'event_date', true);
        $event_location = get_post_meta($post->ID, 'event_location', true);
        $max_participants = get_post_meta($post->ID, 'max_participants', true);
        
        wp_nonce_field('event_details', 'event_details_nonce');
        ?>
        <p>
            <label for="event_date">
                <?php esc_html_e('Event Date', 'tactical-game-organizer'); ?>
            </label><br>
            <input type="date" 
                   id="event_date" 
                   name="event_date" 
                   value="<?php echo esc_attr($event_date); ?>" 
                   class="widefat">
        </p>
        <p>
            <label for="event_location">
                <?php esc_html_e('Location', 'tactical-game-organizer'); ?>
            </label><br>
            <input type="text" 
                   id="event_location" 
                   name="event_location" 
                   value="<?php echo esc_attr($event_location); ?>" 
                   class="widefat">
        </p>
        <p>
            <label for="max_participants">
                <?php esc_html_e('Maximum Participants', 'tactical-game-organizer'); ?>
            </label><br>
            <input type="number" 
                   id="max_participants" 
                   name="max_participants" 
                   value="<?php echo esc_attr($max_participants); ?>" 
                   min="0" 
                   class="widefat">
        </p>
        <?php
    }

    /**
     * Save event details meta box data
     *
     * @param int $post_id Post ID
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

        if (isset($_POST['event_date'])) {
            update_post_meta($post_id, 'event_date', sanitize_text_field($_POST['event_date']));
        }

        if (isset($_POST['event_location'])) {
            update_post_meta($post_id, 'event_location', sanitize_text_field($_POST['event_location']));
        }

        if (isset($_POST['max_participants'])) {
            update_post_meta(
                $post_id, 
                'max_participants', 
                absint($_POST['max_participants'])
            );
        }
    }
} 
<?php

namespace TacticalGameOrganizer\Meta;

use TacticalGameOrganizer\PostTypes\Event;
use TacticalGameOrganizer\PostTypes\Field;

/**
 * Class EventMeta
 * 
 * Handles the registration and management of Event meta fields
 * 
 * @package TacticalGameOrganizer\Meta
 */
class EventMeta {
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', [$this, 'registerMetaFields']);
        add_action('add_meta_boxes', [$this, 'addEventMetaBoxes']);
        add_action('save_post_' . Event::POST_TYPE, [$this, 'saveEventMeta']);
    }

    /**
     * Register meta fields
     *
     * @return void
     */
    public function registerMetaFields(): void {
        register_post_meta(Event::POST_TYPE, 'tgo_event_date', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_post_meta(Event::POST_TYPE, 'tgo_event_field', [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
        ]);

        register_post_meta(Event::POST_TYPE, 'tgo_event_max_participants', [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
            'default' => 0,
        ]);
    }

    /**
     * Add meta boxes to the event edit screen
     *
     * @return void
     */
    public function addEventMetaBoxes(): void {
        add_meta_box(
            'tgo_event_details',
            __('Event Details', 'tactical-game-organizer'),
            [$this, 'renderEventMetaBox'],
            Event::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render the event meta box
     *
     * @param \WP_Post $post The post object
     * @return void
     */
    public function renderEventMetaBox(\WP_Post $post): void {
        wp_nonce_field('tgo_event_meta_box', 'tgo_event_meta_box_nonce');

        $event_date = get_post_meta($post->ID, 'tgo_event_date', true);
        $event_field = get_post_meta($post->ID, 'tgo_event_field', true);
        $max_participants = get_post_meta($post->ID, 'tgo_event_max_participants', true);

        $fields = get_posts([
            'post_type' => Field::POST_TYPE,
            'posts_per_page' => -1,
        ]);

        ?>
        <div class="tgo-meta-box-container">
            <p>
                <label for="tgo_event_date"><?php _e('Event Date:', 'tactical-game-organizer'); ?></label><br>
                <input type="datetime-local" id="tgo_event_date" name="tgo_event_date" 
                       value="<?php echo esc_attr($event_date); ?>">
            </p>

            <p>
                <label for="tgo_event_field"><?php _e('Game Field:', 'tactical-game-organizer'); ?></label><br>
                <select id="tgo_event_field" name="tgo_event_field">
                    <option value=""><?php _e('Select Field', 'tactical-game-organizer'); ?></option>
                    <?php foreach ($fields as $field) : ?>
                        <option value="<?php echo $field->ID; ?>" <?php selected($event_field, $field->ID); ?>>
                            <?php echo esc_html($field->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="tgo_event_max_participants">
                    <?php _e('Maximum Number of Participants:', 'tactical-game-organizer'); ?>
                </label><br>
                <input type="number" id="tgo_event_max_participants" name="tgo_event_max_participants" 
                       value="<?php echo esc_attr($max_participants); ?>" min="0">
            </p>
        </div>
        <?php
    }

    /**
     * Save the event meta
     *
     * @param int $post_id The post ID
     * @return void
     */
    public function saveEventMeta(int $post_id): void {
        if (!isset($_POST['tgo_event_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['tgo_event_meta_box_nonce'], 'tgo_event_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['tgo_event_date'])) {
            update_post_meta(
                $post_id, 
                'tgo_event_date', 
                sanitize_text_field($_POST['tgo_event_date'])
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
    }
} 
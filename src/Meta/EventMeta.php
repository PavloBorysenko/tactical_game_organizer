declare(strict_types=1);

namespace TacticalGameOrganizer\Meta;

use TacticalGameOrganizer\Core\Template;
use TacticalGameOrganizer\PostTypes\Event;
use TacticalGameOrganizer\PostTypes\Field;
use WP_Post;
use function add_action;
use function register_post_meta;
use function add_meta_box;
use function wp_nonce_field;
use function get_post_meta;
use function get_posts;
use function esc_html__;
use function esc_html_e;
use function esc_attr;
use function esc_html;
use function selected;
use function wp_verify_nonce;
use function current_user_can;
use function update_post_meta;
use function sanitize_text_field;
use function intval;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_register_script;
use function wp_register_style;
use function plugins_url;
use function wp_localize_script;

/**
 * Class EventMeta
 * 
 * Handles the registration and management of Event meta fields
 * 
 * @package TacticalGameOrganizer\Meta
 */
class EventMeta extends Template {
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', [$this, 'registerMetaFields']);
        add_action('add_meta_boxes', [$this, 'addEventMetaBoxes']);
        add_action('save_post_' . Event::POST_TYPE, [$this, 'saveEventMeta']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
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
     * Enqueue scripts and styles for the meta box
     *
     * @param string $hook The current admin page
     * @return void
     */
    public function enqueueScripts(string $hook): void {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== Event::POST_TYPE) {
            return;
        }

        // Register and enqueue our custom script
        wp_register_script(
            'tgo-event-meta',
            plugins_url('assets/js/event-meta.js', dirname(__DIR__, 1)),
            ['jquery'],
            '1.0.0',
            true
        );
        wp_enqueue_script('tgo-event-meta');

        // Localize script
        wp_localize_script('tgo-event-meta', 'tgoEventMeta', [
            'i18n' => [
                'selectDateTime' => esc_html__('Please select both date and time for the event.', 'tactical-game-organizer'),
                'selectFutureDate' => esc_html__('Please select a future date and time.', 'tactical-game-organizer')
            ]
        ]);
    }

    /**
     * Add meta boxes to the event edit screen
     *
     * @return void
     */
    public function addEventMetaBoxes(): void {
        add_meta_box(
            'event_details',
            esc_html__('Event Details', 'tactical-game-organizer'),
            [$this, 'renderEventMetaBox'],
            Event::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render the event meta box
     *
     * @param WP_Post $post The post object
     * @return void
     */
    public function renderEventMetaBox(WP_Post $post): void {
        wp_nonce_field('tgo_event_meta_box', 'tgo_event_meta_box_nonce');

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
    }
} 
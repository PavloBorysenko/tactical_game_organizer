<?php

namespace TacticalGameOrganizer\PostTypes;

use function add_action;
use function register_post_type;
use function esc_html__;
use function get_posts;
use function current_user_can;

/**
 * Class Field
 * 
 * Handles the registration and configuration of the Field custom post type
 * 
 * @package TacticalGameOrganizer\PostTypes
 */
class Field {
    /**
     * Post type name
     *
     * @var string
     */
    const POST_TYPE = 'tgo_field';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', [$this, 'registerPostType']);
    }

    /**
     * Register the custom post type
     *
     * @return void
     */
    public function registerPostType(): void {
        $labels = [
            'name'               => \esc_html__('Fields', 'tactical-game-organizer'),
            'singular_name'      => \esc_html__('Field', 'tactical-game-organizer'),
            'add_new'           => \esc_html__('Add New', 'tactical-game-organizer'),
            'add_new_item'      => \esc_html__('Add New Field', 'tactical-game-organizer'),
            'edit_item'         => \esc_html__('Edit Field', 'tactical-game-organizer'),
            'new_item'          => \esc_html__('New Field', 'tactical-game-organizer'),
            'view_item'         => \esc_html__('View Field', 'tactical-game-organizer'),
            'search_items'      => \esc_html__('Search Fields', 'tactical-game-organizer'),
            'not_found'         => \esc_html__('No fields found', 'tactical-game-organizer'),
            'not_found_in_trash'=> \esc_html__('No fields found in trash', 'tactical-game-organizer'),
            'menu_name'         => \esc_html__('Fields', 'tactical-game-organizer'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => ['slug' => 'fields'],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 6,
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'author'],
            'show_in_rest'        => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Get fields owned by a specific user
     * 
     * @param int $user_id User ID
     * @return array Array of field post objects
     */
    public static function getFieldsOwnedByUser($user_id): array {
        if (!$user_id) {
            return [];
        }
        
        $args = [
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'author' => $user_id
        ];
        
        return get_posts($args);
    }
} 
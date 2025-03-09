<?php

namespace TacticalGameOrganizer\PostTypes;

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
            'name'               => __('Fields', 'tactical-game-organizer'),
            'singular_name'      => __('Field', 'tactical-game-organizer'),
            'add_new'           => __('Add New', 'tactical-game-organizer'),
            'add_new_item'      => __('Add New Field', 'tactical-game-organizer'),
            'edit_item'         => __('Edit Field', 'tactical-game-organizer'),
            'new_item'          => __('New Field', 'tactical-game-organizer'),
            'view_item'         => __('View Field', 'tactical-game-organizer'),
            'search_items'      => __('Search Fields', 'tactical-game-organizer'),
            'not_found'         => __('No fields found', 'tactical-game-organizer'),
            'not_found_in_trash'=> __('No fields found in trash', 'tactical-game-organizer'),
            'menu_name'         => __('Fields', 'tactical-game-organizer'),
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
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'        => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }
} 
<?php

namespace TacticalGameOrganizer\PostTypes;

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
    const POST_TYPE = 'tgo_event';

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
            'name'               => __('Events', 'tactical-game-organizer'),
            'singular_name'      => __('Event', 'tactical-game-organizer'),
            'add_new'           => __('Add New', 'tactical-game-organizer'),
            'add_new_item'      => __('Add New Event', 'tactical-game-organizer'),
            'edit_item'         => __('Edit Event', 'tactical-game-organizer'),
            'new_item'          => __('New Event', 'tactical-game-organizer'),
            'view_item'         => __('View Event', 'tactical-game-organizer'),
            'search_items'      => __('Search Events', 'tactical-game-organizer'),
            'not_found'         => __('No events found', 'tactical-game-organizer'),
            'not_found_in_trash'=> __('No events found in trash', 'tactical-game-organizer'),
            'menu_name'         => __('Events', 'tactical-game-organizer'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => ['slug' => 'events'],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'        => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }
} 
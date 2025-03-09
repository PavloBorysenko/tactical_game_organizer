<?php

namespace TacticalGameOrganizer\Core;

use TacticalGameOrganizer\Users\UserFields;
use TacticalGameOrganizer\Users\EventRegistration;

/**
 * Main plugin class
 */
class Plugin {

    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * User fields instance
     *
     * @var UserFields
     */
    private UserFields $user_fields;

    /**
     * Event registration instance
     *
     * @var EventRegistration
     */
    private EventRegistration $event_registration;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function getInstance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct creation
     */
    private function __construct() {
        $this->user_fields = new UserFields();
        $this->event_registration = new EventRegistration();
    }

    /**
     * Initialize plugin
     */
    public function init(): void {
        // Initialize components
        $this->user_fields->init();
        $this->event_registration->init();

        // Register activation hook
        \register_activation_hook(TGO_PLUGIN_FILE, [$this, 'activate']);
    }

    /**
     * Plugin activation
     */
    public function activate(): void {
        // Add capabilities
        $role = \get_role('subscriber');
        if ($role) {
            $role->add_cap('register_for_events');
        }

        // Create custom tables if needed
        $this->createTables();
    }

    /**
     * Create custom tables
     */
    private function createTables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Event registrations table
        $table_name = $wpdb->prefix . 'tgo_event_registrations';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            role varchar(50) NOT NULL,
            team varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_id (event_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        \dbDelta($sql);
    }
} 
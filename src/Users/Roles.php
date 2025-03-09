<?php

namespace TacticalGameOrganizer\Users;

use function add_action;
use function add_role;
use function remove_role;
use function esc_html__;
use function error_log;

/**
 * Class Roles
 * 
 * Manages player role for the tactical game organizer
 */
class Roles {
    /**
     * Player role name
     */
    const ROLE_PLAYER = 'tgo_player';

    /**
     * Available player types
     */
    public static function getPlayerTypes(): array {
        return [
            'commander' => \esc_html__('Commander', 'tactical-game-organizer'),
            'assault'   => \esc_html__('Assault', 'tactical-game-organizer'),
            'sniper'    => \esc_html__('Sniper', 'tactical-game-organizer'),
            'gunner'    => \esc_html__('Gunner', 'tactical-game-organizer'),
            'marksman'  => \esc_html__('Marksman', 'tactical-game-organizer')
        ];
    }

    /**
     * Initialize roles
     */
    public function init(): void {
        \error_log('Tactical Game Organizer: Initializing roles');
        add_action('init', [$this, 'registerRoles']);
    }

    /**
     * Register player role
     */
    public function registerRoles(): void {
        \error_log('Tactical Game Organizer: Registering player role');
        
        // Add player role with extended permissions
        $result = add_role(
            self::ROLE_PLAYER,
            \esc_html__('Player', 'tactical-game-organizer'),
            [
                'read' => true,                      // Allow reading
                'upload_files' => true,              // Allow file uploads
                'edit_posts' => true,                // Allow editing own posts
                'publish_posts' => true,             // Allow publishing own posts
                'edit_published_posts' => true,      // Allow editing published posts
                'read_private_posts' => true,        // Allow reading private posts
                'level_0' => true,                   // Basic access level
            ]
        );

        if (null !== $result) {
            \error_log('Tactical Game Organizer: Player role registered successfully');
        } else {
            \error_log('Tactical Game Organizer: Failed to register player role or role already exists');
        }
    }

    /**
     * Remove player role on plugin deactivation
     */
    public static function removeRoles(): void {
        \error_log('Tactical Game Organizer: Removing player role');
        remove_role(self::ROLE_PLAYER);
    }
} 
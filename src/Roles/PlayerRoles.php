<?php

namespace TacticalGameOrganizer\Roles;

use function add_action;
use function add_role;
use function remove_role;
use function esc_html__;
use function error_log;
use function get_post_meta;
use function update_post_meta;
use function get_user_meta;

class PlayerRoles {
    // Define default role
    public const DEFAULT_ROLE = 'assault';
    
    // Define player role name
    public const ROLE_PLAYER = 'tgo_player';

    // Define available roles with their labels
    private static $roles = [
        'assault' => 'Assault',
        'sniper' => 'Sniper',
        'support' => 'Support',
        'medic' => 'Medic',
        'scout' => 'Scout',
        'engineer' => 'Engineer',
        'commander' => 'Commander',
        'gunner' => 'Gunner',
        'marksman' => 'Marksman'
    ];

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

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getAllRoles(): array {
        $roles = [];
        foreach (self::$roles as $key => $label) {
            $roles[$key] = \esc_html__($label, 'tactical-game-organizer');
        }
        return $roles;
    }

    /**
     * Get role label by key
     *
     * @param string $roleKey
     * @return string
     */
    public static function getRoleLabel(string $roleKey): string {
        $label = self::$roles[$roleKey] ?? $roleKey;
        return \esc_html__($label, 'tactical-game-organizer');
    }

    /**
     * Get roles for select field
     *
     * @return array
     */
    public static function getRolesForSelect(): array {
        $options = [];
        foreach (self::$roles as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => \esc_html__($label, 'tactical-game-organizer')
            ];
        }
        return $options;
    }

    /**
     * Validate if role exists
     *
     * @param string $roleKey
     * @return bool
     */
    public static function isValidRole(string $roleKey): bool {
        return isset(self::$roles[$roleKey]);
    }

    /**
     * Get allowed roles for event
     *
     * @param int $eventId
     * @return array
     */
    public static function getAllowedRolesForEvent(int $eventId): array {
        $allowedRoles = \get_post_meta($eventId, '_tgo_allowed_roles', true);
        $result = [self::DEFAULT_ROLE => \esc_html__(self::$roles[self::DEFAULT_ROLE], 'tactical-game-organizer')]; // Assault is always available
        
        if (empty($allowedRoles)) {
            return self::getAllRoles(); // If no restrictions, return all roles
        }
        
        foreach ($allowedRoles as $roleKey) {
            if (isset(self::$roles[$roleKey])) {
                $result[$roleKey] = \esc_html__(self::$roles[$roleKey], 'tactical-game-organizer');
            }
        }
        
        return $result;
    }

    /**
     * Save allowed roles for event
     *
     * @param int $eventId
     * @param array $roles
     * @return bool
     */
    public static function saveAllowedRolesForEvent(int $eventId, array $roles): bool {
        // Filter only valid roles and ensure assault is always included
        $validRoles = array_filter($roles, [self::class, 'isValidRole']);
        if (!in_array(self::DEFAULT_ROLE, $validRoles)) {
            $validRoles[] = self::DEFAULT_ROLE;
        }
        return \update_post_meta($eventId, '_tgo_allowed_roles', $validRoles);
    }

    /**
     * Get default role for user
     *
     * @param int $userId
     * @return string
     */
    public static function getDefaultRoleForUser(int $userId): string {
        $lastRole = \get_user_meta($userId, '_tgo_last_role', true);
        return $lastRole && self::isValidRole($lastRole) ? $lastRole : self::DEFAULT_ROLE;
    }
} 
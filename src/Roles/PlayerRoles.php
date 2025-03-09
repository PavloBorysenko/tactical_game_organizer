<?php

namespace TacticalGameOrganizer\Roles;

class PlayerRoles {
    // Define default role
    public const DEFAULT_ROLE = 'assault';

    // Define available roles with their labels
    private static $roles = [
        'assault' => 'Assault',
        'sniper' => 'Sniper',
        'support' => 'Support',
        'medic' => 'Medic',
        'scout' => 'Scout',
        'engineer' => 'Engineer',
        'commander' => 'Commander'
    ];

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getAllRoles(): array {
        return self::$roles;
    }

    /**
     * Get role label by key
     *
     * @param string $roleKey
     * @return string
     */
    public static function getRoleLabel(string $roleKey): string {
        return self::$roles[$roleKey] ?? $roleKey;
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
                'label' => $label
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
        $allowedRoles = get_post_meta($eventId, '_tgo_allowed_roles', true);
        $result = [self::DEFAULT_ROLE => self::$roles[self::DEFAULT_ROLE]]; // Assault is always available
        
        if (empty($allowedRoles)) {
            return self::$roles; // If no restrictions, return all roles
        }
        
        foreach ($allowedRoles as $roleKey) {
            if (isset(self::$roles[$roleKey])) {
                $result[$roleKey] = self::$roles[$roleKey];
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
        return update_post_meta($eventId, '_tgo_allowed_roles', $validRoles);
    }

    /**
     * Get default role for user
     *
     * @param int $userId
     * @param array $allowedRoles
     * @return string
     */
    public static function getDefaultRoleForUser(int $userId): string {
        $lastRole = get_user_meta($userId, '_tgo_last_role', true);
        return $lastRole && self::isValidRole($lastRole) ? $lastRole : self::DEFAULT_ROLE;
    }
} 
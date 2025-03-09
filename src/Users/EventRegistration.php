<?php

namespace TacticalGameOrganizer\Users;

/**
 * Class EventRegistration
 * Manages user registration for events
 */
class EventRegistration {

    /**
     * Initialize hooks
     */
    public function init(): void {
        \add_action('init', [$this, 'registerMetaFields']);
        \add_action('tgo_event_registration_form', [$this, 'addRegistrationFields']);
        \add_action('tgo_process_event_registration', [$this, 'processRegistration'], 10, 2);
    }

    /**
     * Register meta fields for event registration
     */
    public function registerMetaFields(): void {
        \register_meta('user', 'event_role', [
            'type' => 'string',
            'description' => \esc_html__('Role for specific event', 'tactical-game-organizer'),
            'single' => true,
            'show_in_rest' => true,
        ]);

        \register_meta('user', 'event_team', [
            'type' => 'string',
            'description' => \esc_html__('Team for specific event', 'tactical-game-organizer'),
            'single' => true,
            'show_in_rest' => true,
        ]);
    }

    /**
     * Add registration fields to event form
     *
     * @param int $event_id Event ID
     */
    public function addRegistrationFields(int $event_id): void {
        $user_id = \get_current_user_id();
        $last_role = UserFields::getLastRole($user_id);
        $last_team = UserFields::getLastTeam($user_id);
        $available_roles = $this->getAvailableRoles($event_id);
        ?>
        <div class="event-registration-fields">
            <p>
                <label for="event_role">
                    <?php \esc_html_e('Role', 'tactical-game-organizer'); ?><br/>
                    <select name="event_role" id="event_role" required>
                        <option value=""><?php \esc_html_e('Select Role', 'tactical-game-organizer'); ?></option>
                        <?php foreach ($available_roles as $role_key => $role_name) : ?>
                            <option value="<?php echo \esc_attr($role_key); ?>" 
                                    <?php \selected($last_role, $role_key); ?>>
                                <?php echo $role_name; // role_name уже экранирован через esc_html__ ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            <p>
                <label for="event_team">
                    <?php \esc_html_e('Team', 'tactical-game-organizer'); ?><br/>
                    <input type="text" 
                           name="event_team" 
                           id="event_team" 
                           value="<?php echo \esc_attr($last_team); ?>" 
                           required 
                    />
                </label>
            </p>
        </div>
        <?php
    }

    /**
     * Process event registration
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     */
    public function processRegistration(int $event_id, int $user_id): void {
        if (!isset($_POST['event_role'], $_POST['event_team'])) {
            \wp_die(\esc_html__('Please fill in all required fields.', 'tactical-game-organizer'));
        }

        $role = \sanitize_text_field($_POST['event_role']);
        $team = \sanitize_text_field($_POST['event_team']);

        // Validate role
        if (!array_key_exists($role, $this->getAvailableRoles($event_id))) {
            \wp_die(\esc_html__('Selected role is not available for this event.', 'tactical-game-organizer'));
        }

        // Save event-specific data
        \update_post_meta($event_id, "participant_{$user_id}_role", $role);
        \update_post_meta($event_id, "participant_{$user_id}_team", $team);

        // Update user's last role and team
        UserFields::updateLastRoleAndTeam($user_id, $role, $team);
    }

    /**
     * Get available roles for event
     *
     * @param int $event_id Event ID
     * @return array
     */
    public function getAvailableRoles(int $event_id): array {
        $roles = UserFields::getRoles();
        $restricted_roles = \get_post_meta($event_id, 'restricted_roles', true);

        if (!empty($restricted_roles) && is_array($restricted_roles)) {
            return array_intersect_key($roles, array_flip($restricted_roles));
        }

        return $roles;
    }

    /**
     * Get participant role for event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @return string
     */
    public static function getParticipantRole(int $event_id, int $user_id): string {
        return \get_post_meta($event_id, "participant_{$user_id}_role", true) ?: '';
    }

    /**
     * Get participant team for event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @return string
     */
    public static function getParticipantTeam(int $event_id, int $user_id): string {
        return \get_post_meta($event_id, "participant_{$user_id}_team", true) ?: '';
    }
} 
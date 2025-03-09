<?php

namespace TacticalGameOrganizer\Users;

use WP_User;
use WP_Error;

/**
 * Class UserFields
 * Manages custom user fields and event registration fields
 */
class UserFields {

    /**
     * Available roles for events
     *
     * @return array
     */
    public static function getRoles(): array {
        return [
            'commander' => \esc_html__('Commander', 'tactical-game-organizer'),
            'assault'   => \esc_html__('Assault', 'tactical-game-organizer'),
            'sniper'    => \esc_html__('Sniper', 'tactical-game-organizer'),
            'gunner'    => \esc_html__('Gunner', 'tactical-game-organizer'),
            'marksman'  => \esc_html__('Marksman', 'tactical-game-organizer')
        ];
    }

    /**
     * Initialize hooks
     */
    public function init(): void {
        // Add custom user fields
        \add_action('show_user_profile', [$this, 'addCustomUserFields']);
        \add_action('edit_user_profile', [$this, 'addCustomUserFields']);
        
        // Save custom user fields
        \add_action('personal_options_update', [$this, 'saveCustomUserFields']);
        \add_action('edit_user_profile_update', [$this, 'saveCustomUserFields']);
        
        // Add phone to registration
        \add_action('register_form', [$this, 'addPhoneToRegistration']);
        \add_action('user_register', [$this, 'savePhoneOnRegistration']);
        \add_filter('registration_errors', [$this, 'validatePhoneOnRegistration'], 10, 3);
    }

    /**
     * Add custom fields to user profile
     *
     * @param WP_User $user User object
     */
    public function addCustomUserFields(WP_User $user): void {
        ?>
        <h3><?php \esc_html_e('Player Information', 'tactical-game-organizer'); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="callsign"><?php \esc_html_e('Callsign', 'tactical-game-organizer'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="callsign" 
                           id="callsign" 
                           value="<?php echo \esc_attr(\get_user_meta($user->ID, 'callsign', true)); ?>" 
                           class="regular-text" 
                    />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="phone"><?php \esc_html_e('Phone', 'tactical-game-organizer'); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           value="<?php echo \esc_attr(\get_user_meta($user->ID, 'phone', true)); ?>" 
                           class="regular-text" 
                           required 
                    />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save custom user fields
     *
     * @param int $user_id User ID
     */
    public function saveCustomUserFields(int $user_id): void {
        if (!\current_user_can('edit_user', $user_id)) {
            return;
        }

        \update_user_meta($user_id, 'callsign', \sanitize_text_field($_POST['callsign'] ?? ''));
        \update_user_meta($user_id, 'phone', \sanitize_text_field($_POST['phone'] ?? ''));
    }

    /**
     * Add phone field to registration form
     */
    public function addPhoneToRegistration(): void {
        ?>
        <p>
            <label for="phone">
                <?php \esc_html_e('Phone', 'tactical-game-organizer'); ?><br/>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       class="input" 
                       value="<?php echo \esc_attr($_POST['phone'] ?? ''); ?>" 
                       required 
                />
            </label>
        </p>
        <?php
    }

    /**
     * Save phone on user registration
     *
     * @param int $user_id User ID
     */
    public function savePhoneOnRegistration(int $user_id): void {
        if (isset($_POST['phone'])) {
            \update_user_meta($user_id, 'phone', \sanitize_text_field($_POST['phone']));
        }
    }

    /**
     * Validate phone on registration
     *
     * @param WP_Error $errors Error object
     * @param string $sanitized_user_login Username
     * @param string $user_email User email
     * @return WP_Error
     */
    public function validatePhoneOnRegistration(WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error {
        if (empty($_POST['phone'])) {
            $errors->add('phone_error', \esc_html__('Please enter your phone number.', 'tactical-game-organizer'));
        }
        return $errors;
    }

    /**
     * Get user's last role
     *
     * @param int $user_id User ID
     * @return string
     */
    public static function getLastRole(int $user_id): string {
        return \get_user_meta($user_id, 'last_role', true) ?: '';
    }

    /**
     * Get user's last team
     *
     * @param int $user_id User ID
     * @return string
     */
    public static function getLastTeam(int $user_id): string {
        return \get_user_meta($user_id, 'last_team', true) ?: '';
    }

    /**
     * Update user's last role and team
     *
     * @param int $user_id User ID
     * @param string $role Role
     * @param string $team Team
     */
    public static function updateLastRoleAndTeam(int $user_id, string $role, string $team): void {
        \update_user_meta($user_id, 'last_role', \sanitize_text_field($role));
        \update_user_meta($user_id, 'last_team', \sanitize_text_field($team));
    }
} 
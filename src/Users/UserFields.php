<?php

namespace TacticalGameOrganizer\Users;

use WP_User;
use WP_Error;

/**
 * Class UserFields
 * Manages custom user fields for players
 */
class UserFields {

    /**
     * Initialize hooks
     */
    public function init(): void {
        // Добавляем поля в профиль пользователя
        \add_action('show_user_profile', [$this, 'addCustomUserFields']);
        \add_action('edit_user_profile', [$this, 'addCustomUserFields']);
        
        // Сохраняем поля профиля
        \add_action('personal_options_update', [$this, 'saveCustomUserFields']);
        \add_action('edit_user_profile_update', [$this, 'saveCustomUserFields']);
        
        // Добавляем поля при регистрации
        \add_action('register_form', [$this, 'addRegistrationFields']);
        \add_action('user_register', [$this, 'saveRegistrationFields']);
        \add_filter('registration_errors', [$this, 'validateRegistrationFields'], 10, 3);
    }

    /**
     * Add custom fields to user profile
     *
     * @param WP_User $user User object
     */
    public function addCustomUserFields(WP_User $user): void {
        $player_type = \get_user_meta($user->ID, 'player_type', true);
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
                           required 
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
            <tr>
                <th>
                    <label for="player_type"><?php \esc_html_e('Player Type', 'tactical-game-organizer'); ?></label>
                </th>
                <td>
                    <select name="player_type" id="player_type" required>
                        <option value=""><?php \esc_html_e('Select Player Type', 'tactical-game-organizer'); ?></option>
                        <?php foreach (Roles::getPlayerTypes() as $type => $label) : ?>
                            <option value="<?php echo \esc_attr($type); ?>" 
                                    <?php \selected($player_type, $type); ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Add fields to registration form
     */
    public function addRegistrationFields(): void {
        ?>
        <p>
            <label for="callsign">
                <?php \esc_html_e('Callsign', 'tactical-game-organizer'); ?><br/>
                <input type="text" 
                       name="callsign" 
                       id="callsign" 
                       class="input" 
                       value="<?php echo \esc_attr($_POST['callsign'] ?? ''); ?>" 
                       required 
                />
            </label>
        </p>
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
        <p>
            <label for="player_type">
                <?php \esc_html_e('Player Type', 'tactical-game-organizer'); ?><br/>
                <select name="player_type" id="player_type" class="input" required>
                    <option value=""><?php \esc_html_e('Select Player Type', 'tactical-game-organizer'); ?></option>
                    <?php foreach (Roles::getPlayerTypes() as $type => $label) : ?>
                        <option value="<?php echo \esc_attr($type); ?>" 
                                <?php \selected($_POST['player_type'] ?? '', $type); ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
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
        \update_user_meta($user_id, 'player_type', \sanitize_text_field($_POST['player_type'] ?? ''));
    }

    /**
     * Save registration fields
     *
     * @param int $user_id User ID
     */
    public function saveRegistrationFields(int $user_id): void {
        if (isset($_POST['callsign'])) {
            \update_user_meta($user_id, 'callsign', \sanitize_text_field($_POST['callsign']));
        }
        if (isset($_POST['phone'])) {
            \update_user_meta($user_id, 'phone', \sanitize_text_field($_POST['phone']));
        }
        if (isset($_POST['player_type'])) {
            \update_user_meta($user_id, 'player_type', \sanitize_text_field($_POST['player_type']));
        }

        // Устанавливаем роль игрока
        $user = new \WP_User($user_id);
        $user->set_role(Roles::ROLE_PLAYER);
    }

    /**
     * Validate registration fields
     *
     * @param WP_Error $errors Error object
     * @param string $sanitized_user_login Username
     * @param string $user_email User email
     * @return WP_Error
     */
    public function validateRegistrationFields(WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error {
        if (empty($_POST['callsign'])) {
            $errors->add('callsign_error', \esc_html__('Please enter your callsign.', 'tactical-game-organizer'));
        }
        if (empty($_POST['phone'])) {
            $errors->add('phone_error', \esc_html__('Please enter your phone number.', 'tactical-game-organizer'));
        }
        if (empty($_POST['player_type'])) {
            $errors->add('player_type_error', \esc_html__('Please select your player type.', 'tactical-game-organizer'));
        } elseif (!array_key_exists($_POST['player_type'], Roles::getPlayerTypes())) {
            $errors->add('player_type_error', \esc_html__('Invalid player type selected.', 'tactical-game-organizer'));
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

    /**
     * Get user's last callsign
     *
     * @param int $user_id User ID
     * @return string
     */
    public static function getLastCallsign(int $user_id): string {
        return \get_user_meta($user_id, 'callsign', true) ?: '';
    }

    /**
     * Update user's last callsign
     *
     * @param int $user_id User ID
     * @param string $callsign Callsign
     */
    public static function updateLastCallsign(int $user_id, string $callsign): void {
        \update_user_meta($user_id, 'callsign', \sanitize_text_field($callsign));
    }
} 
<?php

namespace TacticalGameOrganizer\Users;

use TacticalGameOrganizer\Users\UserFields;
use TacticalGameOrganizer\Users\Roles;
use TacticalGameOrganizer\PostTypes\Event;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class EventRegistration
 * Handles event registration functionality
 */
class EventRegistration {
    /**
     * Initialize hooks
     */
    public function init(): void {
        // Add form to event content
        \add_filter('the_content', [$this, 'addRegistrationForm']);
        
        // Register REST API endpoints
        \add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    /**
     * Register REST API routes
     */
    public function registerRestRoutes(): void {
        \register_rest_route(
            'tactical-game-organizer/v1',
            '/events/(?P<event_id>\d+)/participants',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getParticipants'],
                'permission_callback' => '__return_true',
                'args' => [
                    'event_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return \is_numeric($param) && \get_post_type($param) === Event::POST_TYPE;
                        }
                    ]
                ]
            ]
        );

        \register_rest_route(
            'tactical-game-organizer/v1',
            '/events/(?P<event_id>\d+)/register',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'handleRegistration'],
                'permission_callback' => [$this, 'checkPermissions'],
                'args' => [
                    'event_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return \is_numeric($param) && \get_post_type($param) === Event::POST_TYPE;
                        }
                    ],
                    'callsign' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    ],
                    'role' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function($param) {
                            return \array_key_exists($param, Roles::getPlayerTypes());
                        }
                    ],
                    'team' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ]
            ]
        );

        \register_rest_route(
            'tactical-game-organizer/v1',
            '/events/(?P<event_id>\d+)/cancel',
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'handleCancellation'],
                'permission_callback' => [$this, 'checkPermissions'],
                'args' => [
                    'event_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return \is_numeric($param) && \get_post_type($param) === Event::POST_TYPE;
                        }
                    ]
                ]
            ]
        );
    }

    /**
     * Get participants list
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function getParticipants(WP_REST_Request $request) {
        $event_id = $request->get_param('event_id');
        $participants = \get_post_meta($event_id, 'participants', true) ?: [];
        $current_user_id = \get_current_user_id();
        $max_participants = \get_post_meta($event_id, 'max_participants', true) ?: 0;
        
        $participants_data = [];
        foreach ($participants as $user_id) {
            $callsign = UserFields::getLastCallsign($user_id);
            $role = UserFields::getLastRole($user_id);
            $team = UserFields::getLastTeam($user_id);
            
            $role_label = Roles::getPlayerTypes()[$role] ?? $role;
            
            $participants_data[] = [
                'user_id' => $user_id,
                'callsign' => $callsign,
                'role' => $role,
                'role_label' => $role_label,
                'team' => $team,
                'can_cancel' => $user_id === $current_user_id && \is_user_logged_in()
            ];
        }

        return new WP_REST_Response([
            'participants' => $participants_data,
            'max_participants' => (int)$max_participants,
            'current_count' => count($participants),
            'has_available_slots' => $max_participants === 0 || count($participants) < $max_participants
        ], 200);
    }

    /**
     * Check if user has permission to register/cancel
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function checkPermissions(WP_REST_Request $request) {
        if (!\is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                \esc_html__('You must be logged in to register for events.', 'tactical-game-organizer'),
                ['status' => 401]
            );
        }

        $user = \wp_get_current_user();
        if (!\in_array('tgo_player', (array) $user->roles)) {
            return new WP_Error(
                'rest_forbidden',
                \esc_html__('Only players can register for events.', 'tactical-game-organizer'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Handle event registration
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handleRegistration(WP_REST_Request $request) {
        $event_id = $request->get_param('event_id');
        $callsign = $request->get_param('callsign');
        $role = $request->get_param('role');
        $team = $request->get_param('team');
        $user_id = \get_current_user_id();

        // Проверяем количество участников
        $participants = \get_post_meta($event_id, 'participants', true) ?: [];
        $max_participants = \get_post_meta($event_id, 'max_participants', true) ?: 0;
        
        if ($max_participants > 0 && count($participants) >= $max_participants) {
            return new WP_Error(
                'event_full',
                \esc_html__('This event is full. No more registrations are accepted.', 'tactical-game-organizer'),
                ['status' => 400]
            );
        }

        // Update user meta
        UserFields::updateLastCallsign($user_id, $callsign);
        UserFields::updateLastRoleAndTeam($user_id, $role, $team);

        // Add user to participants
        if (!\in_array($user_id, $participants)) {
            $participants[] = $user_id;
            \update_post_meta($event_id, 'participants', $participants);
        }

        return new WP_REST_Response([
            'message' => \esc_html__('Successfully registered for the event.', 'tactical-game-organizer')
        ], 200);
    }

    /**
     * Handle registration cancellation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handleCancellation(WP_REST_Request $request) {
        $event_id = $request->get_param('event_id');
        $user_id = \get_current_user_id();

        // Remove user from participants
        $participants = \get_post_meta($event_id, 'participants', true) ?: [];
        $participants = \array_diff($participants, [$user_id]);
        \update_post_meta($event_id, 'participants', $participants);

        return new WP_REST_Response([
            'message' => \esc_html__('Successfully cancelled registration.', 'tactical-game-organizer')
        ], 200);
    }

    /**
     * Add registration form to event content
     *
     * @param string $content Post content
     * @return string
     */
    public function addRegistrationForm(string $content): string {
        // Only add form to event post type
        if (!\is_singular(Event::POST_TYPE)) {
            return $content;
        }

        // Start output buffering
        \ob_start();

        // Display original content
        echo '<div class="tgo-event-content">';
        echo $content;
        echo '</div>';

        // Get event data
        $event_id = \get_the_ID();
        $participants = \get_post_meta($event_id, 'participants', true) ?: [];
        $max_participants = \get_post_meta($event_id, 'max_participants', true) ?: 0;
        $user_id = \get_current_user_id();
        
        echo '<div class="tgo-event-registration-container">';

        // Check if user can register
        if (!\is_user_logged_in()) {
            $this->renderParticipantList($event_id);
            echo '<div class="tgo-form-container">';
            echo \esc_html__('Please log in to register for events.', 'tactical-game-organizer');
            echo '</div>';
            echo '</div>';
            return \ob_get_clean();
        }

        $user = \wp_get_current_user();
        if (!\in_array('tgo_player', (array) $user->roles)) {
            $this->renderParticipantList($event_id);
            echo '<div class="tgo-form-container">';
            echo \esc_html__('Only players can register for events.', 'tactical-game-organizer');
            echo '</div>';
            echo '</div>';
            return \ob_get_clean();
        }

        // Сначала показываем список участников
        $this->renderParticipantList($event_id);

        // Показываем форму регистрации только если:
        // 1. Пользователь еще не зарегистрирован
        // 2. Есть свободные места (max_participants = 0 означает без ограничений)
        if (!\in_array($user_id, $participants) && 
            ($max_participants === 0 || count($participants) < $max_participants)) {
            $this->renderRegistrationForm($event_id);
        } elseif ($max_participants > 0 && count($participants) >= $max_participants && !\in_array($user_id, $participants)) {
            echo '<div class="tgo-form-container">';
            echo \esc_html__('This event is full. No more registrations are accepted.', 'tactical-game-organizer');
            echo '</div>';
        }

        echo '</div>';

        // Return buffered content
        return \ob_get_clean();
    }

    /**
     * Render the registration form
     *
     * @param int $event_id Event ID
     */
    private function renderRegistrationForm(int $event_id): void {
        $user_id = \get_current_user_id();
        $last_callsign = UserFields::getLastCallsign($user_id);
        $last_role = UserFields::getLastRole($user_id);
        $last_team = UserFields::getLastTeam($user_id);
        ?>
        <form id="tgo-event-registration" class="tgo-form">
            <input type="hidden" name="event_id" value="<?php echo \esc_attr($event_id); ?>">

            <div class="tgo-form-field">
                <label for="callsign"><?php \esc_html_e('Callsign', 'tactical-game-organizer'); ?></label>
                <input type="text" 
                       id="callsign" 
                       name="callsign" 
                       value="<?php echo \esc_attr($last_callsign); ?>" 
                       required>
            </div>

            <div class="tgo-form-field">
                <label for="role"><?php \esc_html_e('Player Role', 'tactical-game-organizer'); ?></label>
                <select id="role" name="role" required>
                    <?php foreach (Roles::getPlayerTypes() as $type => $label) : ?>
                        <option value="<?php echo \esc_attr($type); ?>" 
                                <?php \selected($last_role ?: 'assault', $type); ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tgo-form-field">
                <label for="team"><?php \esc_html_e('Team', 'tactical-game-organizer'); ?></label>
                <input type="text" 
                       id="team" 
                       name="team" 
                       value="<?php echo \esc_attr($last_team ?: \esc_html__('No Team', 'tactical-game-organizer')); ?>" 
                       required>
            </div>

            <button type="submit" class="tgo-button">
                <?php \esc_html_e('Register for Event', 'tactical-game-organizer'); ?>
            </button>
        </form>

        <div id="tgo-registration-message" style="display: none;"></div>
        <?php
    }

    /**
     * Render the participant list
     *
     * @param int $event_id Event ID
     */
    private function renderParticipantList(int $event_id): void {
        echo '<div class="tgo-participant-list" data-event-id="' . \esc_attr($event_id) . '"></div>';
    }
} 
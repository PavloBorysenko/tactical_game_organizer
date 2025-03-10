<?php
/**
 * Template for event roles meta box
 * 
 * @package TacticalGameOrganizer
 * @var array $allowedRoles Currently allowed roles for this event
 * @var array $allRoles All available roles
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tgo-meta-box-container">
    <div class="field-group">
        <label><?php \esc_html_e('Select roles that will be available for this event', 'tactical-game-organizer'); ?></label>
        <div class="role-list">
            <?php foreach ($allRoles as $role => $label) : ?>
                <div class="role-item">
                    <input type="checkbox" 
                           name="tgo_allowed_roles[]" 
                           id="role_<?php echo \esc_attr($role); ?>" 
                           value="<?php echo \esc_attr($role); ?>"
                           <?php \checked(isset($allowedRoles[$role])); ?>>
                    <label for="role_<?php echo \esc_attr($role); ?>" style="display: inline;">
                        <?php echo \esc_html($label); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <span class="description">
            <?php \esc_html_e('Players will only be able to select from these roles when registering', 'tactical-game-organizer'); ?>
        </span>
    </div>
</div> 
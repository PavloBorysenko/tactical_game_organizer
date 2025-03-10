<?php
/**
 * Template for event meta box
 * 
 * @package TacticalGameOrganizer
 * @var string $event_date Event date in Y-m-d H:i format
 * @var int $event_field
 * @var int $max_participants
 * @var array $fields
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Split datetime into date and time parts
$date_parts = $event_date ? explode(' ', $event_date) : ['', ''];
$date = $date_parts[0];
$time = isset($date_parts[1]) ? substr($date_parts[1], 0, 5) : '08:30';

// Get current date if no date is set
if (empty($date)) {
    $date = \current_time('Y-m-d');
}

// Generate time options
function generate_time_options($start, $end, $interval = 15) {
    $times = [];
    $current = strtotime($start);
    $end_time = strtotime($end);

    while ($current <= $end_time) {
        $times[] = date('H:i', $current);
        $current = strtotime('+' . $interval . ' minutes', $current);
    }

    return $times;
}

$time_options = generate_time_options('08:00', '22:00');
?>

<!-- TEST MARKER: <?php echo date('Y-m-d H:i:s'); ?> -->

<div class="tgo-meta-box-container">
    <div class="field-group event-datetime-container">
        <div class="event-datetime">
            <label for="tgo_event_date"><?php \esc_html_e('Event Date', 'tactical-game-organizer'); ?></label>
            <input type="date" 
                   id="tgo_event_date" 
                   name="tgo_event_date" 
                   value="<?php echo \esc_attr($date); ?>"
                   min="<?php echo \esc_attr(\current_time('Y-m-d')); ?>"
                   required>
        </div>

        <div class="event-datetime">
            <label for="tgo_event_time"><?php \esc_html_e('Start Time', 'tactical-game-organizer'); ?></label>
            <select id="tgo_event_time" 
                    name="tgo_event_time" 
                    required>
                <?php foreach ($time_options as $option) : ?>
                    <option value="<?php echo \esc_attr($option); ?>" 
                            <?php \selected($time, $option); ?>>
                        <?php echo \esc_html($option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="description">
                <?php \esc_html_e('Time interval: 15 minutes', 'tactical-game-organizer'); ?>
            </span>
        </div>
    </div>

    <div class="field-group">
        <label for="tgo_event_field"><?php \esc_html_e('Game Field', 'tactical-game-organizer'); ?></label>
        <select id="tgo_event_field" name="tgo_event_field" required>
            <option value=""><?php \esc_html_e('Select Field', 'tactical-game-organizer'); ?></option>
            <?php foreach ($fields as $field) : ?>
                <option value="<?php echo $field->ID; ?>" <?php \selected($event_field, $field->ID); ?>>
                    <?php echo \esc_html($field->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field-group">
        <label for="tgo_event_max_participants">
            <?php \esc_html_e('Maximum Number of Participants', 'tactical-game-organizer'); ?>
        </label>
        <input type="number" 
               id="tgo_event_max_participants" 
               name="tgo_event_max_participants" 
               value="<?php echo \esc_attr($max_participants); ?>" 
               min="0"
               required>
        <span class="description">
            <?php \esc_html_e('Set to 0 for unlimited participants', 'tactical-game-organizer'); ?>
        </span>
    </div>
</div>

<style>
.event-datetime-container {
    margin-bottom: 20px;
}
.event-datetime {
    display: inline-block;
    margin-right: 20px;
}
.event-datetime input,
.event-datetime select {
    width: 200px;
    padding: 5px;
}
.event-datetime .description {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
</style> 
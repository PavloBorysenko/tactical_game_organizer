<?php
/**
 * Template for displaying single event
 * 
 * @package TacticalGameOrganizer
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>

                <div class="event-header">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('full', ['class' => 'event-featured-image']); ?>
                    <?php endif; ?>

                    <?php 
                    // Get event details
                    $event_date = get_post_meta(get_the_ID(), 'tgo_event_date', true);
                    $event_field_id = get_post_meta(get_the_ID(), 'tgo_event_field', true);
                    $max_participants = get_post_meta(get_the_ID(), 'tgo_event_max_participants', true);
                    
                    // Format date and time
                    $date_time = new \DateTime($event_date);
                    $current_time = new \DateTime();
                    $is_active = $date_time > $current_time;
                    ?>

                    <div class="event-badges">
                        <div class="event-badge date">
                            <i class="dashicons dashicons-calendar-alt"></i>
                            <?php echo \esc_html($date_time->format('d.m.Y')); ?>
                        </div>
                        
                        <div class="event-badge time">
                            <i class="dashicons dashicons-clock"></i>
                            <?php echo \esc_html($date_time->format('H:i')); ?>
                        </div>

                        <div class="event-badge status <?php echo $is_active ? 'active' : 'expired'; ?>">
                            <i class="dashicons <?php echo $is_active ? 'dashicons-yes' : 'dashicons-no'; ?>"></i>
                            <?php echo $is_active ? 
                                \esc_html__('Active', 'tactical-game-organizer') : 
                                \esc_html__('Expired', 'tactical-game-organizer'); 
                            ?>
                        </div>
                    </div>
                </div>

                <div class="event-content">
                    <div class="event-details">
                        <div class="event-detail-item">
                            <span class="event-detail-label"><?php \esc_html_e('Date', 'tactical-game-organizer'); ?></span>
                            <span class="event-detail-value"><?php echo \esc_html($date_time->format('d.m.Y')); ?></span>
                        </div>
                        
                        <div class="event-detail-item">
                            <span class="event-detail-label"><?php \esc_html_e('Time', 'tactical-game-organizer'); ?></span>
                            <span class="event-detail-value"><?php echo \esc_html($date_time->format('H:i')); ?></span>
                        </div>

                        <?php if ($event_field_id) : 
                            $field = get_post($event_field_id);
                        ?>
                            <div class="event-detail-item">
                                <span class="event-detail-label"><?php \esc_html_e('Game Field', 'tactical-game-organizer'); ?></span>
                                <span class="event-detail-value"><?php echo \esc_html($field->post_title); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="event-detail-item">
                            <span class="event-detail-label"><?php \esc_html_e('Maximum Participants', 'tactical-game-organizer'); ?></span>
                            <span class="event-detail-value"><?php echo \esc_html($max_participants); ?></span>
                        </div>
                    </div>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </main>
</div>

<?php
get_sidebar();
get_footer(); 
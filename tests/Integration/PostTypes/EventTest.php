<?php

namespace TacticalGameOrganizer\Tests\Integration\PostTypes;

use TacticalGameOrganizer\PostTypes\Event;
use WP_UnitTestCase;

class EventTest extends WP_UnitTestCase {
    private Event $event;

    public function setUp(): void {
        parent::setUp();
        $this->event = new Event();
    }

    public function test_post_type_registered() {
        $post_types = get_post_types();
        $this->assertArrayHasKey(Event::POST_TYPE, $post_types);
    }

    public function test_create_event() {
        $post_id = $this->factory->post->create([
            'post_type' => Event::POST_TYPE,
            'post_title' => 'Test Event',
            'post_content' => 'Test content',
        ]);

        $this->assertNotNull(get_post($post_id));
        $this->assertEquals('Test Event', get_post($post_id)->post_title);
    }

    public function test_event_meta() {
        $post_id = $this->factory->post->create([
            'post_type' => Event::POST_TYPE,
        ]);

        update_post_meta($post_id, 'tgo_event_date', '2025-03-09 10:00:00');
        update_post_meta($post_id, 'tgo_event_max_participants', 20);

        $this->assertEquals('2025-03-09 10:00:00', get_post_meta($post_id, 'tgo_event_date', true));
        $this->assertEquals(20, get_post_meta($post_id, 'tgo_event_max_participants', true));
    }
} 
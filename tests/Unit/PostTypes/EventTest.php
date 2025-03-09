<?php

namespace TacticalGameOrganizer\Tests\Unit\PostTypes;

use TacticalGameOrganizer\PostTypes\Event;
use WP_Mock;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testConstruct() {
        WP_Mock::expectActionAdded('init', [new Event(), 'registerPostType']);
        new Event();
        $this->assertConditionsMet();
    }

    public function testRegisterPostType() {
        $event = new Event();

        // Mock WordPress functions
        WP_Mock::userFunction('__', [
            'times' => 11,
            'return' => 'translated text',
        ]);

        WP_Mock::userFunction('register_post_type', [
            'times' => 1,
            'args' => [
                Event::POST_TYPE,
                $this->callback(function ($args) {
                    return is_array($args) &&
                           isset($args['public']) &&
                           $args['public'] === true &&
                           isset($args['show_in_rest']) &&
                           $args['show_in_rest'] === true;
                }),
            ],
        ]);

        $event->registerPostType();
        $this->assertConditionsMet();
    }
} 
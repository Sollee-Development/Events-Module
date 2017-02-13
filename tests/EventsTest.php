<?php
use Events\Model\{Events};

class EventsTest extends PHPUnit_Framework_TestCase {

    public function testGetEvents() {
        $events = new Events(
            new MockEventsStorage([
                ['start_date' => '2017-2-10']
            ]),
            new MockEventsStorage([
                ['start_date' => '2017-2-16']
            ]),
            new MockEventsStorage([
                ['start_date' => '2017-2-4']
            ])
        );

        $iterator = $events->getEvents(2017, 2); // Technically the parameters don't matter for
                                            // this test because of the way the mock storage works

        $this->assertEquals([
            ['start_date' => '2017-2-10'],
            ['start_date' => '2017-2-16'],
            ['start_date' => '2017-2-4']
        ], iterator_to_array($iterator, false));
    }

    public function testGetUpcomingEvents() {
        $events = new Events(
            new MockEventsStorage([
                (object)['start_date' => '2017-2-10']
            ]),
            new MockEventsStorage([
                (object)['start_date' => '2017-2-16']
            ]),
            new MockEventsStorage([
                (object)['start_date' => '2017-2-4']
            ])
        );

        $iterator = $events->getUpcomingEvents(2);

        $this->assertEquals([
            (object)['start_date' => '2017-2-4'],
            (object)['start_date' => '2017-2-10']
        ], iterator_to_array($iterator));
    }
}

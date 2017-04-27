<?php
use Events\Model\{Events, Sorter};

class EventsTest extends PHPUnit_Framework_TestCase {
    private function getMonthDates($year, $month) {
        $start = new \DateTime($year . '-' . $month);
        $end = (new \DateTime($year . '-' . $month))->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));
        return [$start, $end];
    }

    public function testGetEvents() {
        $events = new Events(new Sorter(),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-10')]
            ]),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-16')]
            ]),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-4')]
            ])
        );

        $iterator = $events->getEvents(...$this->getMonthDates(2017, 2)); // Technically the parameters don't matter for
                                            // this test because of the way the mock storage works

        $this->assertEquals([
            (object)['start_date' => new \DateTime('2017-2-4')],
            (object)['start_date' => new \DateTime('2017-2-10')],
            (object)['start_date' => new \DateTime('2017-2-16')]
        ], iterator_to_array($iterator, false));
    }

    public function testUpcomingEvents() {
        $events = new Events(new Sorter(),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-10')]
            ]),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-16')]
            ]),
            new MockEventsStorage([
                (object)['start_date' => new \DateTime('2017-2-4')]
            ])
        );

        $iterator = $events->getEvents(new \DateTime('0:0'), null, 2);

        $this->assertEquals([
            (object)['start_date' => new \DateTime('2017-2-4')],
            (object)['start_date' => new \DateTime('2017-2-10')]
        ], iterator_to_array($iterator));
    }
}

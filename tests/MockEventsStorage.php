<?php
class MockEventsStorage implements Events\Model\EventsStorage {
    private $array;

    public function __construct(array $array) {
        $this->array = $array;
    }

    public function getEvents($year, $month): \Iterator {
        return new ArrayIterator($this->array);
    }

    public function getUpcomingEvents($num): \Iterator {
        return new ArrayIterator($this->array);
    }
}

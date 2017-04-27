<?php
class MockEventsStorage implements Events\Model\EventsStorage {
    private $array;

    public function __construct(array $array) {
        $this->array = $array;
    }

    public function getEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
        return new ArrayIterator($this->array);
    }
}

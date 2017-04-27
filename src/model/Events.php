<?php

namespace Events\Model;

class Events implements EventsStorage {
    private $eventsStorage;
    private $sorter;

    public function __construct(Sorter $sorter, EventsStorage ...$eventsStorage) {
        $this->eventsStorage = $eventsStorage;
        $this->sorter = $sorter;
    }

    public function getEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
        $events = new \AppendIterator();
        foreach ($this->eventsStorage as $event)
            $events->append($event->getEvents($from, $to, $num));

        $events = iterator_to_array($events, false);
        usort($events, [$this->sorter, 'compareEvents']);

        return new \ArrayIterator($num ? array_slice($events, 0, $num) : $events);
    }

    public function getUpcomingEvents($num): \Iterator {
        $now = new \DateTime('0:0');
        return $this->getEvents($now, null, $num);
    }
}

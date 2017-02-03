<?php

namespace Events\Model;

class Events implements EventsStorage {
    private $eventsStorage;

    public function __construct(EventsStorage ...$eventsStorage) {
        $this->eventsStorage = $eventsStorage;
    }

    public function getEvents($year, $month): \Iterator {
        $events = new \AppendIterator();
        foreach ($this->eventsStorage as $event)
            $events->append($event->getEvents($year, $month));

        return $events;
   }

    public function getUpcomingEvents($num): \Iterator {
        $events = new \AppendIterator();
        
        foreach ($this->eventsStorage as $event)
            $events->append($event->getUpcomingEvents($num));

        $events = iterator_to_array($events, false);

        usort($events, function ($event1, $event2) {
            $date1 = new \DateTime($event1->start_date);
            $date2 = new \DateTime($event2->start_date);
            if ($date1 === $date2) return 0;
            else return ($date1 < $date2) ? -1 : 1;
        });

        return new \ArrayIterator(array_slice($events, 0, $num));
    }
}

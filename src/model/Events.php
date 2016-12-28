<?php

namespace Events\Model;

class Events {
    private $single;
    private $repeating;

    public function __construct(SingleEvents $single, RepeatingEvents $repeating) {
        $this->single = $single;
        $this->repeating = $repeating;
    }

    public function get_events($year, $month) {
        $single_events = $this->single->getEvents($year, $month);

        $events = new \AppendIterator();
        $events->append($single_events);
        $events->append($this->repeating->getEvents($year, $month)->getIterator());

        return $events;
   }

    public function getUpcomingEvents() {
        return $this->single->getUpcomingEvents();
    }
}

<?php

namespace Events\Model;

interface EventsStorage {
    public function getEvents($year, $month): \Iterator;
    public function getUpcomingEvents($num): \Iterator;
}

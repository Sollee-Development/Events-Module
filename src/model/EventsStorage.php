<?php

namespace Events\Model;

interface EventsStorage {
    public function getEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator;
}

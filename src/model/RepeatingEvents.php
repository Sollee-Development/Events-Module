<?php
namespace Events\Model;
class RepeatingEvents implements EventsStorage {
    private $mapper;
    private $rrule;
    private $transformer;

    public function __construct(\Maphper\Maphper $mapper, RRule $rrule, \Recurr\Transformer\ArrayTransformer $transformer) {
        $this->mapper = $mapper;
        $this->rrule = $rrule;
        $this->transformer = $transformer;
    }

    private function getOccurrences($constraint, $rule, $event) {
        $events = [];
        foreach ($this->transformer->transform($rule, $constraint) as $occurrence) {
            $day = $occurrence->getStart();
            $dayString = $day->format('Y-m-d');
            $event = clone $event;
            $event->start_date = $dayString;
            $event->end_date = $dayString;
            $events[] = $event;
        }
        return $events;
    }

    private function getRecurringFromDatabase($endAfter, $startBefore) {
        return $this->mapper->filter([
           \Maphper\Maphper::FIND_NOT => [
               'repeat_id' => null
           ],
           \Maphper\Maphper::FIND_LESS | \Maphper\Maphper::FIND_EXACT => [
               'start_date' => $startBefore
           ],
           \Maphper\Maphper::FIND_OR => [
               \Maphper\Maphper::FIND_GREATER => [
                   'end_date' => $endAfter
               ],
               'end_date' => null
           ]
        ]);
    }

    private function getEventOccurrencesList($repeatingEvents, $constraint, $countLimit = null) {
        $events = [];
        foreach ($repeatingEvents as $event) {
            $currentRule = $this->rrule->getRule($event);
            if ($countLimit && $currentRule->getCount() > $num) $currentRule->setCount($num);

            $events = array_merge($events, $this->getOccurrences($constraint, $currentRule, $event));
        }
        return $events;
    }

    private function getConstraint($before = false, $after = false) {
        if ($before && $after && $after > $before) return new \Recurr\Transformer\Constraint\BetweenConstraint($before, $after, true);
        else if ($after) return  new \Recurr\Transformer\Constraint\AfterConstraint($after, true);
        else if ($before) return new \Recurr\Transformer\Constraint\BeforeConstraint($before, true);
        else return null;
    }

    private function retrieveEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
        $constraint = $this->getConstraint($to, $from);
        $repeatingEvents = $this->getRecurringFromDatabase($from, $to ?? $from);
        $events = $this->getEventOccurrencesList($repeatingEvents, $constraint, $num);

        usort($events, function ($event1, $event2) {
            $date1 = new \DateTime($event1->start_date);
            $date2 = new \DateTime($event2->start_date);
            if ($date1 === $date2) return 0;
            else return ($date1 < $date2) ? -1 : 1;
        });

        return new \ArrayIterator($num ? array_slice($events, 0, $num) : $events);
    }

    public function getEvents($year, $month): \Iterator {
        $start = new \DateTime($year . '-' . $month);
        $end = (new \DateTime($year. '-' . $month))->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));
        return $this->retrieveEvents($start, $end);
    }

    public function getUpcomingEvents($num): \Iterator {
        $now = new \DateTime('0:0');
        return $this->retrieveEvents($now, null, $num);
    }
}

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

    public function getEvents($year, $month): \Iterator {
        $start = new \DateTime($year . '-' . $month, new \DateTimeZone('America/New_York'));
        $end = new \DateTime($year. '-' . $month, new \DateTimeZone('America/New_York'));
        $end->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));

        $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($start, $end, true);


        $repeatingEvents = $this->getRecurringFromDatabase($start, $end);
        $events = [];

        foreach ($repeatingEvents as $event) {
            $currentRule = $this->rrule->getRule($event);
            $events = array_merge($events, $this->getOccurrences($constraint, $currentRule, $event));
        }

       return new \ArrayIterator($events);
    }

    public function getUpcomingEvents($num): \Iterator {
        $now = (new \DateTime())->setTime(0, 0);
        $constraint = new \Recurr\Transformer\Constraint\AfterConstraint($now, true);
        $repeatingEvents = $this->getRecurringFromDatabase($now, $now);

        $events = [];

        foreach ($repeatingEvents as $event) {
            $currentRule = $this->rrule->getRule($event);
            if ($currentRule->getCount() > $num) $currentRule->setCount($num);

            $events = array_merge($events, $this->getOccurrences($constraint, $currentRule, $event));
        }

        usort($events, function ($event1, $event2) {
            $date1 = new \DateTime($event1->start_date);
            $date2 = new \DateTime($event2->start_date);
            if ($date1 === $date2) return 0;
            else return ($date1 < $date2) ? -1 : 1;
        });

        return new \ArrayIterator(array_slice($events, 0, $num));
    }
}

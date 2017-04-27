<?php
namespace Events\Model;
class RepeatingEvents implements EventsStorage {
    private $mapper;
    private $rrule;
    private $transformer;
    private $sorter;

    public function __construct(\Maphper\Maphper $mapper, RRule $rrule, \Recurr\Transformer\ArrayTransformer $transformer, Sorter $sorter) {
        $this->mapper = $mapper;
        $this->rrule = $rrule;
        $this->transformer = $transformer;
        $this->sorter = $sorter;
    }

    private function getOccurrences($constraint, $rule, $event) {
        $events = [];
        foreach ($this->transformer->transform($rule, $constraint) as $occurrence) {
            $day = $occurrence->getStart();
            $event = clone $event;
            $event->start_date = $event->end_date = $day;
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

    public function getEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
        $constraint = $this->getConstraint($to, $from);
        $repeatingEvents = $this->getRecurringFromDatabase($from, $to ?? $from);
        $events = $this->getEventOccurrencesList($repeatingEvents, $constraint, $num);

        usort($events, [$this->sorter, 'compareEvents']);

        return new \ArrayIterator($num ? array_slice($events, 0, $num) : $events);
    }
}

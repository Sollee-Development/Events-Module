<?php
namespace Events\Model;
class RepeatingEvents implements EventsStorage {
    private $mapper;
    private $rrule;
    private $transformer;

    public function __construct(\Maphper\Maphper $mapper, \Recurr\Rule $rrule, \Recurr\Transformer\ArrayTransformer $transformer) {
        $this->mapper = $mapper;
        $this->rrule = $rrule;
        $this->transformer = $transformer;
    }

    public function getEvents($year, $month): \Iterator {
        $start = new \DateTime($year . '-' . $month, new \DateTimeZone('America/New_York'));
        $end = new \DateTime($year. '-' . $month, new \DateTimeZone('America/New_York'));
        $end->add(new \DateInterval('P1M'));
        $end->sub(new \DateInterval('P1D'));

        $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($start, $end, true);

        $repeatingEvents = $this->mapper->filter([
           \Maphper\Maphper::FIND_NOT => [
               'repeat_id' => null
           ],
           \Maphper\Maphper::FIND_OR => [
               \Maphper\Maphper::FIND_LESS => [
                   'start_date' => $end
               ],
               'start_date' => $end
           ],
           [
               \Maphper\Maphper::FIND_OR => [
                   \Maphper\Maphper::FIND_GREATER => [
                       'end_date' => $start
                   ],
                   'end_date' => null
               ]
           ]
        ]);

        $events = new \ArrayObject();

        foreach ($repeatingEvents as $event) {
            $currentRule = $this->getRule($event);

            foreach ($this->transformer->transform($currentRule, $constraint) as $occurrence) {
                $day = $occurrence->getStart();
                $dayString = $day->format('Y-m-d');
                $event = clone $event;
                $event->start_date = $dayString;
                $event->end_date = $dayString;
                $events[] = $event;
            }
        }

       return $events->getIterator();
    }

    private function getRule($event) {
        $repeatSettings = $event->repeat;
        $currentRule = clone $this->rrule;

        if ($event->end_date) $currentRule->setUntil(new \DateTime($event->end_date));
        $currentRule->setStartDate(new \DateTime($event->start_date))
            ->setFreq(strtoupper($repeatSettings->freq))->setInterval($repeatSettings->interval_num);

        return $currentRule;
    }

    public function getUpcomingEvents($num): \Iterator {
        $now = (new \DateTime())->setTime(0, 0);
        $repeatingEvents = $this->mapper->filter([
            \Maphper\Maphper::FIND_NOT => [
               'repeat_id' => null
            ],
            \Maphper\Maphper::FIND_OR => [
                \Maphper\Maphper::FIND_LESS => [
                    'start_date' => $now
                ],
                'start_date' => $now
            ],
            [
                \Maphper\Maphper::FIND_OR => [
                    \Maphper\Maphper::FIND_GREATER => [
                        'end_date' => $now
                    ],
                    'end_date' => null
                ]
            ]
        ])->sort('start_date asc');

        $events = [];

        foreach ($repeatingEvents as $event) {
            $currentRule = $this->getRule($event);
            if ($currentRule->getRule() > $num) $currentRule->setCount($num);

            foreach ($this->transformer->transform($currentRule) as $occurrence) {
                $day = $occurrence->getStart();
                $dayString = $day->format('Y-m-d');
                $event = clone $event;
                $event->start_date = $dayString;
                $event->end_date = $dayString;
                $events[] = $event;
            }
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

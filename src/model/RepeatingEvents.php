<?php
namespace Events\Model;
class RepeatingEvents {
    private $mapper;
    private $rrule;
    private $transformer;

    public function __construct(\Maphper\Maphper $mapper, \Recurr\Rule $rrule, \Recurr\Transformer\ArrayTransformer $transformer) {
        $this->mapper = $mapper;
        $this->rrule = $rrule;
        $this->transformer = $transformer;
    }

    public function getEvents($year, $month) {
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
            $repeatSettings = $event->repeat;
            $currentRule = clone $this->rrule;

            if ($event->end_date) $currentRule->setUntil(new \DateTime($event->end_date));
            $currentRule->setStartDate(new \DateTime($event->start_date))
                ->setFreq(strtoupper($repeatSettings->freq))->setInterval($repeatSettings->interval_num);


            foreach ($this->transformer->transform($currentRule, $constraint) as $occurrence) {
                $day = $occurrence->getStart();
               $dayString = $day->format('Y-m-d');
               $event = clone $event;
               $event->start_date = $dayString;
               $event->end_date = $dayString;
               $events[] = $event;
            }
        }

       return $events;
    }
}

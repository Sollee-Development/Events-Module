<?php
namespace Events\Model;
class SingleEvents implements EventsStorage {
    private $mapper;
    private $when;

    public function __construct(\Maphper\Maphper $mapper) {
        $this->mapper = $mapper;
    }

    public function getEvents($year, $month): \Iterator {
        $start = new \DateTime($year . '-' . $month, new \DateTimeZone('America/New_York'));
        $end = new \DateTime($year. '-' . $month, new \DateTimeZone('America/New_York'));
        $end->add(new \DateInterval('P1M'));
        $end->sub(new \DateInterval('P1D'));

        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        $single_events = $this->mapper->filter([
            'repeat_id' => null,
            \Maphper\Maphper::FIND_BETWEEN => [
                'start_date' => [$start, $end]
            ]
         ])->sort('start_date asc');

         return $single_events;
    }

    public function getUpcomingEvents($num): \Iterator {
        return $this->mapper->filter([
            \Maphper\Maphper::FIND_GREATER | \Maphper\Maphper::FIND_EXACT => [
                'start_date' => (new \DateTime())->setTime(0, 0)
            ]
        ])->sort('start_date asc')->limit($num);
    }
}

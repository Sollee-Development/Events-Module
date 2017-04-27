<?php
namespace Events\Model;
use Maphper\Maphper;
class SingleEvents implements EventsStorage {
    private $mapper;
    private $when;

    public function __construct(\Maphper\Maphper $mapper) {
        $this->mapper = $mapper;
    }

    private function retrieveEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
        $filter = [
            [],// Start Date
            []// End Date
        ];
        if ($from) {
            $filter[0][Maphper::FIND_GREATER | Maphper::FIND_EXACT] = ['start_date' => $from];
            $filter[1][Maphper::FIND_GREATER | Maphper::FIND_EXACT] = ['end_date' => $from];
        }
        if ($to) {
            $filter[0][Maphper::FIND_LESS | Maphper::FIND_EXACT] = ['start_date' => $to];
            $filter[1][Maphper::FIND_LESS | Maphper::FIND_EXACT] = ['end_date' => $to];
        }
        return $this->mapper->filter([Maphper::FIND_OR => $filter])->sort('start_date asc')->limit($num);
    }

    public function getEvents($year, $month): \Iterator {
        $start = new \DateTime($year . '-' . $month);
        $end = new \DateTime($year. '-' . $month);
        $end->add(new \DateInterval('P1M'));
        $end->sub(new \DateInterval('P1D'));

        return $this->retrieveEvents($start, $end);
    }

    public function getUpcomingEvents($num): \Iterator {
        $now = new \DateTime('0:0');
        return $this->retrieveEvents($now, null, $num);
    }
}

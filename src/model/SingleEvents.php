<?php
namespace Events\Model;
use Maphper\Maphper;
class SingleEvents implements EventsStorage {
    private $mapper;
    private $when;

    public function __construct(\Maphper\Maphper $mapper) {
        $this->mapper = $mapper;
    }

    public function getEvents(\DateTimeInterface $from = null, \DateTimeInterface $to = null, $num = null): \Iterator {
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
        if (!$from && !$to) $filter = [];
        
        return $this->mapper->filter(['repeat_id' => null, Maphper::FIND_OR => $filter])->sort('start_date asc')->limit($num)->getIterator();
    }
}

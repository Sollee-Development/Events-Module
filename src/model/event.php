<?php

namespace Events\Model;

class Event {
    private $maphper;

    public function __construct(\Maphper\Maphper $maphper) {
        $this->maphper = $maphper;
    }

    public function get_events($year, $month) {
       $start = new \DateTime($year . '-' . $month, new \DateTimeZone('America/New_York'));
       $end = new \DateTime($year. '-' . $month, new \DateTimeZone('America/New_York'));
       $end->add(new \DateInterval('P1M'));
       $end->sub(new \DateInterval('P1D'));

       $start = $start->format('Y-m-d');
       $end = $end->format('Y-m-d');

       return $this->maphper->filter([
          \Maphper\Maphper::FIND_OR => [
              \Maphper\Maphper::FIND_OR => [
                  \Maphper\Maphper::FIND_BETWEEN => [
                      'start_date' => [$start, $end]
                  ],
                  \Maphper\Maphper::FIND_BETWEEN => [
                      'end_date' => [$start, $end]
                  ]
              ]
          ]
       ])->sort('start_date asc');
    }

    public function getUpcomingEvents() {
        return $this->maphper->filter([
            \Maphper\Maphper::FIND_OR => [
                \Maphper\Maphper::FIND_GREATER => [
                    'start_date' => (new \DateTime())->setTime(0, 0)
                ],
                'start_date' => (new \DateTime())->setTime(0, 0)
            ]
        ])->sort('start_date asc');
    }
}

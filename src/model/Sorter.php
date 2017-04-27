<?php
namespace Events\Model;
class Sorter {
    public function compareEvents($event1, $event2) {
        $date1 = new \DateTime($event1->start_date);
        $date2 = new \DateTime($event2->start_date);
        if ($date1 === $date2) return 0;
        else return ($date1 < $date2) ? -1 : 1;
    }
}

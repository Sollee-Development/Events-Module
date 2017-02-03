<?php

namespace Events\Model;

class Calendar implements \MVC\Model\Filterable {
	private $model;
    private $filter;
	private $headings = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    private $dayStartTime;

	public function __construct(EventsStorage $model) {
		$this->model = $model;
        $this->dayStartTime = new \DateTimeImmutable('7:00 AM');
	}

	public function setFilter($filter) {
		$this->filter = $filter;
	}

	private function maxEvents($events) {
        return array_reduce(array_map("count", $events), "max", 1);
	}

    private function datesEqual(\DateTimeInterface $obj1, \DateTimeInterface $obj2, string $format) {
        return $obj1->format($format) === $obj2->format($format);
    }

    /*
     * Turns an Iterator into an array with each index being a day of the month
     * containing an array of events with each event's index being its position on the calander
     * so an event that spans multiple days is in the same position on the calendar for all the days
     */
	private function makeEventsArray($origEvents, $date) {
		$events = array();

		foreach ($origEvents as $row) {
            $endDay = new \DateTime($row->end_date . ' ' . $row->end_time);
            $startDay = new \DateTimeImmutable($row->start_date);

            if ($endDay < $this->dayStartTime->modify($row->end_date))
                $endDay->modify('-1 day');

            $row_posit = isset($events[$startDay->format('j')]) ?
                            $this->getNextAvailiblePosition($events[$startDay->format('j')]) : 0;

            $endDay->setTime(0, 0);
            $loopDay = new \DateTime($row->start_date);
            while ($loopDay <= $endDay) {
                $event_row = clone $row;
                if ($loopDay == $startDay) $event_row->day_type = 'first';
                else if ($loopDay == $endDay) $event_row->day_type = 'last';
                if ($this->datesEqual($loopDay, $date, "n")) $events[$loopDay->format('j')][$row_posit] = (array)$event_row;
                $loopDay->modify("+1 day");
            }
		}

		return $events;
	}

    private function getNextAvailiblePosition(array $day) {
        for ($i = 0;;$i++) if (!isset($day[$i])) return $i;
    }

    /*
     * This turns the result of the makeEventsArray function into a calendar ready
     * array with a row for each row of the calendar and each day in the row is an
     * array that contains the day number and an array of events to display for that day
     * with multiple day events ocupying the same position on all occuring days
     */
	private function eventsCalendarReady($events, $calendarDate) {
		$table = [array_fill_keys($this->headings, '')];
        $date = new \DateTime('@' . $calendarDate->getTimestamp());

		$max_events = $this->maxEvents($events);

        while ($this->datesEqual($date, $calendarDate, "m")) {
            if (count($table[count($table)-1]) > 0 && $date->format('j') != 1 && $date->format('N') == 7) {
                $table[] = array_fill_keys($this->headings, '');
            }

            $table[count($table)-1][$date->format('l')] = $this->getDay($events, $date->format('j'), $max_events);
            $date->modify('+1 Day');
        }

		return $table;
	}

    private function getDay($events, $list_day, $max_events) {
        $day = [
            'day_number' => $list_day,
            'events' => array_replace(array_fill(0, $max_events, []), $events[$list_day] ?? [])
        ];
        return $day;
    }

  	public function getData() {
		$month = $this->filter['month'];
		$year = $this->filter['year'];

        $date = new \DateTimeImmutable($year . '-' . $month);

		$origEvents = $this->model->getEvents($year, $month);
		$events = $this->makeEventsArray($origEvents, $date);
		$calendar = $this->eventsCalendarReady($events, $date);

		return ['calendar' => $calendar, 'date' => $date];
	}

}

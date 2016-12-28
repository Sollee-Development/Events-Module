<?php

namespace Events\Model;

class Lists implements \MVC\Model\Filterable {
	private $model;
    private $filter;
	private $headings = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    const DAY_START_TIME = '7:00 AM';

	public function __construct(Events $model) {
		$this->model = $model;
	}

	public function setFilter($filter) {
		$this->filter = $filter;
	}

	private function max_events($events) {
		$max_events = 1;
		foreach ($events as $day) {
			if ($max_events < count($day))
				$max_events = count($day);
		}
	    return $max_events;
	}

	private function makeEventsArray($orig_events, $year, $month) {
		$events = array();

		foreach ($orig_events as $orig_row) {
			$row = (array) $orig_row;
		  $end_date = date('j', strtotime($row['end_date']));
		  if ($month == date("n",strtotime($row['start_date'])) and $year == date("Y", strtotime($row['start_date']))) {
			  $start_date = date('j', strtotime($row['start_date']));
			  if ($end_date < $start_date) $end_date = date('t', strtotime($row['start_date']));
		  } elseif ($month == date("n",strtotime($row['end_date'])) and $year == date("Y", strtotime($row['end_date']))) {
			  $start_date = 1;
		  }

      		if ((new \DateTime($row['end_date'] . ' ' . $row['end_time'])) < (new \DateTime($row['end_date'] . ' ' . self::DAY_START_TIME)) &&
              $end_date == date('j', strtotime($row['end_date']))) $end_date -= 1;

		  for ($i = 0; ; $i++) {
			  if (!isset($events[$start_date][$i])) {
				  $row_posit = $i;
				  break;
			  }
		  }

		  for ($i = $start_date; $i <= $end_date; $i++) {
			  $events[$i][(int)$row_posit] = $row;
		  }
		}

		return $events;
	}

	private function eventsCalendarReady($events, $year, $month) {
		$table = [];
        $date = new \DateTime($year . '-' . $month . ' -01');

        $table[] = array_fill_keys($this->headings, '');

		$max_events = $this->max_events($events);

        while ($date->format('m') == $month) {
            if (count($table[count($table)-1]) > 0 && $date->format('j') != 1 && $date->format('N') == 7) {// echo 'test';
                $table[] = array_fill_keys($this->headings, '');
            }

			//////////////////////////////////////////////////////////////////
			$list_day = $date->format('j');

			$day = ['day_number' => $list_day, 'events' => array_fill(0, $max_events, [])];


	        if (isset($events[$list_day])) foreach ($events[$list_day] as $position => $row) {
	          if (isset($events[$list_day - 1][$position]) &&
			        $events[$list_day][$position] == $events[$list_day - 1][$position]) {
			        if (isset($events[$list_day + 1][$position]) &&
				        $events[$list_day][$position] == $events[$list_day + 1][$position]) { /* If day before and after is set*/
				        $day_type = 'middle-day';
			        } else { /* If just day before */
				        $day_type = 'last-day';
			        }
		        } elseif (isset($events[$list_day + 1][$position]) &&
			        $events[$list_day][$position] == $events[$list_day + 1][$position]) { /* Just day after */
			        $day_type = 'first-day';
		        } else {
			        $day_type = 'single-day';
		        }


	          $day['events'][$position] = array_merge($events[$list_day][$position], ['day_type' => $day_type]);
	        }

			//////////////////////////////////////////////////////

            $table[count($table)-1][$date->format('l')] = $day; //$date->format('d');
						//var_dump($table);
            $date->modify('+1 Day');
        }

		return $table;
	}

	private function getOtherCalendarStuff($year, $month) {
		$previous = ($month != 1 ? $year : $year - 1) . '/' . ($month != 1 ? $month - 1 : 12);
		$next = ($month != 12 ? $year : $year + 1) . '/' . ($month != 12 ? $month + 1 : 1);

		$year_range = 7; $year_select = [];
		for ($x = ($year-floor($year_range/2)); $x <= ($year+floor($year_range/2)); $x++) {
			$year_select[] = $x;
		}

		return ['previous' => $previous, 'next' => $next, 'year_select' => $year_select, 'year' => $year,
			'month' => $month, 'datetime' => $year . '-' . ($month < 10 ? '0' . $month : $month)];
	}

  	public function getData() {
		$month = $this->filter['month'];
		$year = $this->filter['year'];

		$orig_events = $this->model->get_events($year, $month);

		$events = $this->makeEventsArray($orig_events, $year, $month);

		$dates_array = $this->eventsCalendarReady($events, $year, $month);

		return ['calendar' => $dates_array,
		          'cal_links' => $this->getOtherCalendarStuff($year, $month)];

	}

}

?>

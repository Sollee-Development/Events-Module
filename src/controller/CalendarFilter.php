<?php
namespace Events\Controller;
class CalendarFilter implements \MVC\Model\Filterable {
    private $model;

	public function __construct(\Events\Model\Calendar $model) {
		$this->model = $model;
	}

	public function filter(...$filter) {
        if (empty($filter)) {
            $date_time = new \DateTime();
            $filter[0] = $date_time->format('Y');
            $filter[1] = $date_time->format('n');
        }
		$this->model->setFilter(array_combine(['year', 'month'], $filter));
	}
}

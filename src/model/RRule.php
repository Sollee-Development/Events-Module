<?php

namespace Events\Model;

class RRule {
    private $rrule;

    public function __construct(\Recurr\Rule $rrule) {
        $this->rrule = $rrule;
    }

    public function getRule($event) {
        $repeatSettings = $event->repeat;
        $currentRule = clone $this->rrule;

        if ($event->end_date) $currentRule->setUntil(new \DateTime($event->end_date));
        $currentRule->setStartDate(new \DateTime($event->start_date))
            ->setFreq(strtoupper($repeatSettings->freq))->setInterval($repeatSettings->interval_num);

        return $currentRule;
    }
}

<?php
namespace Events\Model;
class Event implements \MVC\Model\Idable {
    private $data;
    private $transformer;
    private $rule;

    public function __construct(\MVC\Model\Id $data, \Recurr\Transformer\TextTransformer $transformer, \Recurr\Rule $rule) {
        $this->data = $data;
        $this->transformer = $transformer;
        $this->rule = $rule;
    }

    public function setId($id) {
        $this->data->setId($id);
    }

    public function getData() {
        return $this->data->getData();
    }

    public function getRepeatText() {
        $event = $this->data->getData();
        $repeatSettings = $event->repeat;
        if ($event->end_date) $this->rule->setUntil(new \DateTime($event->end_date));
        $this->rule->setStartDate(new \DateTime($event->start_date))
            ->setFreq(strtoupper($repeatSettings->freq))->setInterval($repeatSettings->interval_num);
            
        return $this->transformer->transform($this->rule);
    }
}

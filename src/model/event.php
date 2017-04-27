<?php
namespace Events\Model;
class Event implements \MVC\Model\Idable {
    private $data;
    private $transformer;
    private $rule;

    public function __construct(\MVC\Model\Id $data, \Recurr\Transformer\TextTransformer $transformer, RRule $rule) {
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
        if (empty($this->getData()->repeat)) return "";
        $event = $this->getData();
        return $this->transformer->transform($this->rule->getRule($event));
    }
}

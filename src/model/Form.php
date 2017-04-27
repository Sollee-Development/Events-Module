<?php
namespace Events\Model;
class Form implements \MVC\Model\Form {
    private $saver;
    public $submitted = false;
    public $sucessful = false;
    public $data;

    public function __construct(\MVC\Model\Form\Save $saver) {
        $this->saver = $saver;
    }

    public function main($data) {
        $this->eventSaver->main($data);
        $this->data = $this->eventSaver->data;
    }

    public function submit($data) {
        $this->submitted = true;
        foreach ($data as $key => $value) {
            if (is_array($value)) continue;
            $obj = \DateTime::createFromFormat("g:i A", $value);
            if ($obj && $obj->format("g:i A") == $value) $data[$key] = $obj->format("H:i");
        }
        unset($data["recurring"]);
        if (empty($data["end_date"])) $data["end_date"] = null;
        return $this->eventSaver->submit($data);
    }

    public function success() {
        $this->successful = true;
    }
}

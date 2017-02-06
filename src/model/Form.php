<?php
namespace Events\Model;
class Form implements \MVC\Model\Form {
    private $eventSaver;
    private $repeatSaver;
    public $event_djs;
    public $submitted = false;
    public $sucessful = false;
    public $data;

    public function __construct(\MVC\Model\Form\Save $eventSaver, \MVC\Model\Form\Save $repeatSaver) {
        $this->eventSaver = $eventSaver;
        $this->repeatSaver = $repeatSaver;
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
        if (isset($data["recurring"]) && $data["recurring"] === "true") { // If it is a recurring event
            $repeatData = $data["repeat"];
            //$this->repeatSaver->submit($repeatData);
            //$data["repeat_id"] = $this->repeatSaver->data->id;
        }
        //unset($data["repeat"]);
        unset($data["recurring"]);
        if (empty($data["end_date"])) $data["end_date"] = null;
        return $this->eventSaver->submit($data);
    }

    public function success() {
        $this->successful = true;
    }
}

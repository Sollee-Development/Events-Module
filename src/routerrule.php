<?php

namespace Events;

class RouterRule implements \Level2\Router\Rule {
    private $dice;

    public function __construct(\Dice\Dice $dice) {
        $this->dice = $dice;
    }

    public function find(array $route) {
        if ($route[0] !== "events") return false;
        if (empty($route[1])) {
            $date_time = $this->dice->create('DateTime');
            $route[1] = $date_time->format('Y');
            $route[2] = $date_time->format('n');
        }
        if (isset($route[3])) return false;

        //if (isset($_GET['month'])) header("Location: /elemukulek/events/{$_GET['year']}/{$_GET['month']}");

        if (!empty($route[2]) && ($route[1] !== "edit" && $route[1] !== "delete")) { // If it is a calendar
            $this->dice->addRule('$model', [
                'instanceOf' => 'Events\\Model\\Lists',
                'substitutions' => ['Maphper\\Maphper' => ['instance' => ['MaphperLoader\\Json', 'getMaphper'], 'params' => ['events']]]
            ]);
            $model = $this->dice->create('$model');
            $controller = $this->dice->create('MVC\\Controller\\Filter', [], [$model]);
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/calendar.tss"]);

            $controller->filter(['year' => $route[1], 'month' => $route[2]]);
        } elseif ($route[1] == "upcoming") {

            $model = $this->dice->create('Events\\Model\\Event');
            $controller = null;
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/upcoming_page.tss"]);

        } elseif ($route[1] == "create") {
            $this->dice->addRule('$model', [
                "instanceOf" => "MVC\\Model\\Form\\Save",
                "constructParams" => [
                    ["instance" => ["MaphperLoader\\Json", "getMaphper"], "params" => ["events"]],
                    ["instance" => '$events_validate_event']
                ]
            ]);
            $model = $this->dice->create('$model');
            $controller = $this->dice->create('MVC\\Controller\\Form', [], [$model]);
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/form/create.tss"]);

            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                $controller->submit();
            }
            else {
                $controller->main();
            }

        } elseif ($route[1] == "edit" && isset($route[2]) && is_numeric($route[2])) {

            $this->dice->addRule('$model', [
                "instanceOf" => "MVC\\Model\\Form\\Save",
                "constructParams" => [
                    ["instance" => ["MaphperLoader\\Json", "getMaphper"], "params" => ["events"]],
                    ["instance" => '$events_validate_event']
                ]
            ]);
            $model = $this->dice->create('$model');
            $controller = $this->dice->create('MVC\\Controller\\Form', [], [$model]);
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/form/edit.tss"]);

            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                $controller->submit();
            }
            else {
                $controller->main([$route[2]]);
            }

        } elseif ($route[1] == "delete" && isset($route[2]) && is_numeric($route[2])) {

            $this->dice->addRule('$model', [
                "instanceOf" => "MVC\\Model\\Form\\Delete",
                "constructParams" => [
                    ["instance" => ["MaphperLoader\\Json", "getMaphper"], "params" => ["events"]]
                ]
            ]);
            $model = $this->dice->create('$model');
            $controller = $this->dice->create('MVC\\Controller\\Form', [], [$model]);
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/form/delete.tss"]);

            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                $controller->submit();
            }
            else {
                $controller->main([$route[2]]);
            }

        } else { // If it is an event
            if (!is_numeric($route[1])) {
                $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "html:header[location] { content: '../events'; }"]);
                return new \Level2\Router\Route(null, $view, null, getcwd());
            }
            $this->dice->addRule('$model', [
                'instanceOf' => 'MVC\\Model\\Id',
                'substitutions' => ['Maphper\\Maphper' => ['instance' => ['MaphperLoader\\Json', 'getMaphper'], 'params' => ['events']]]
            ]);
            $model = $this->dice->create('$model');
            $controller = $this->dice->create('MVC\\Controller\\Id', [], [$model]);
            $view = $this->dice->create('Transphporm\\Builder', ["Layouts/layout.xml", "Modules/events/view/view_event.tss"]);
            $controller->id($route[1]);
            //return false; // return false until this is added
        }

        $route = new \Level2\Router\Route($model, $view, $controller, getcwd());
        return $route;
    }
}
//*/
?>

<?php
use Events\Model\{EventsStorage, Calendar};

class CalendarTest extends PHPUnit_Framework_TestCase {

    private function getData($events) {
        $storage = new MockEventsStorage($events);
        $calendar = new Calendar($storage);
        $calendar->setFilter(['year' => 2017, 'month' => 2]);
        return $calendar->getData()['calendar'];
    }

    public function testEmptyCalendar() {
        $data = $this->getData([]);

        $this->assertEquals(5, count($data)); // There are 5 weeks in Feb 2017
        foreach ($data as $row) {
            $this->assertEquals(7, count($row)); // Ensure that all rows have 7 days
        }

        // There are 3 blank days in the first week
        $this->assertEquals('', $data[0]['Sunday']);
        $this->assertEquals('', $data[0]['Monday']);
        $this->assertEquals('', $data[0]['Tuesday']);
        $this->assertEquals(1, $data[0]['Wednesday']['day_number']);
    }

    public function testNormalCalendar() {
        $event = (object) [
            'start_date' => '2017-2-8',
            'end_date' => '2017-2-8',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $dataRow = $data[1];
        $this->assertArraySubset((array)$event, $dataRow['Wednesday']['events'][0]);
    }

    public function testMultiDayEvent() {
        $event = (object) [
            'start_date' => '2017-2-8',
            'end_date' => '2017-2-10',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $dataRow = $data[1];
        $this->assertEmpty($dataRow['Tuesday']['events'][0]);
        $this->assertArraySubset((array)$event, $dataRow['Wednesday']['events'][0]);
        $this->assertEquals('first', $dataRow['Wednesday']['events'][0]['day_type']);
        $this->assertArraySubset((array)$event, $dataRow['Thursday']['events'][0]);
        $this->assertFalse(isset($dataRow['Thursday']['events'][0]['day_type']));
        $this->assertArraySubset((array)$event, $dataRow['Friday']['events'][0]);
        $this->assertEquals('last', $dataRow['Friday']['events'][0]['day_type']);
    }

    public function testEventAtEndOfCalendar() {
        $event = (object) [
            'start_date' => '2017-2-27',
            'end_date' => '2017-3-2',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $this->assertEmpty($data[0]['Sunday']);
        $this->assertEmpty($data[0]['Wednesday']['events'][0]);
        $this->assertEmpty($data[4]['Sunday']['events'][0]);
        $this->assertEquals('first', $data[4]['Monday']['events'][0]['day_type']);
        $this->assertFalse(isset($data[4]['Tuesday']['events'][0]['day_type']));
        $this->assertEmpty($data[4]['Wednesday']);
    }

    public function testEventAtBeginningOfCalendar() {
        $event = (object) [
            'start_date' => '2017-1-20',
            'end_date' => '2017-2-2',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $this->assertEmpty($data[0]['Tuesday']);
        $this->assertEmpty($data[4]['Tuesday']['events'][0]);
        $this->assertArraySubset((array)$event, $data[0]['Wednesday']['events'][0]);
        $this->assertFalse(isset($data[0]['Wednesday']['events'][0]['day_type']));
        $this->assertArraySubset((array)$event, $data[0]['Thursday']['events'][0]);
        $this->assertEquals('last', $data[0]['Thursday']['events'][0]['day_type']);
    }

    public function testDayStartTime() {
        $event = (object) [
            'start_date' => '2017-2-8',
            'end_date' => '2017-2-10',
            'start_time' => '12:00',
            'end_time' => '6:00', // Day Start is 7 so set time before that
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $dataRow = $data[1];
        $this->assertEmpty($dataRow['Tuesday']['events'][0]);
        $this->assertArraySubset((array)$event, $dataRow['Wednesday']['events'][0]);
        $this->assertEquals('first', $dataRow['Wednesday']['events'][0]['day_type']);
        $this->assertArraySubset((array)$event, $dataRow['Thursday']['events'][0]);
        $this->assertEquals('last', $dataRow['Thursday']['events'][0]['day_type']);
        $this->assertEmpty($dataRow['Friday']['events'][0]);
    }

    public function testDayStartTimeAtEndOfMonth() {
        $event = (object) [
            'start_date' => '2017-2-27',
            'end_date' => '2017-3-2',
            'start_time' => '12:00',
            'end_time' => '6:00', // Day Start is 7 so set time before that
            'name' => 'E1'
        ];
        $data = $this->getData([$event]);

        $dataRow = $data[4];
        $this->assertEmpty($dataRow['Sunday']['events'][0]);
        $this->assertArraySubset((array)$event, $dataRow['Monday']['events'][0]);
        $this->assertEquals('first', $dataRow['Monday']['events'][0]['day_type']);
        $this->assertArraySubset((array)$event, $dataRow['Tuesday']['events'][0]);
        $this->assertFalse(isset($dataRow['Tuesday']['events'][0]['day_type']));
        $this->assertEmpty($dataRow['Wednesday']);
    }

    public function testSameDayEvents() {
        $event1 = (object) [
            'start_date' => '2017-2-7',
            'end_date' => '2017-2-9',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E1'
        ];
        $event2 = (object) [
            'start_date' => '2017-2-8',
            'end_date' => '2017-2-10',
            'start_time' => '12:00',
            'end_time' => '12:00',
            'name' => 'E2'
        ];
        $data = $this->getData([$event1, $event2]);

        $dataRow = $data[1];

        foreach ($data as $row) {
            foreach ($row as $day) {
                if (is_array($day)) $this->assertEquals(2, count($day['events'])); // Ensure that all days have 2 events spots
            }
        }

        // Check first event
        $this->assertEmpty($dataRow['Monday']['events'][0]);
        $this->assertArraySubset((array)$event1, $dataRow['Tuesday']['events'][0]);
        $this->assertEquals('first', $dataRow['Tuesday']['events'][0]['day_type']);
        $this->assertArraySubset((array)$event1, $dataRow['Wednesday']['events'][0]);
        $this->assertFalse(isset($dataRow['Wednesday']['events'][0]['day_type']));
        $this->assertArraySubset((array)$event1, $dataRow['Thursday']['events'][0]);
        $this->assertEquals('last', $dataRow['Thursday']['events'][0]['day_type']);

        //Check second event
        $this->assertArraySubset((array)$event2, $dataRow['Wednesday']['events'][1]);
        $this->assertEquals('first', $dataRow['Wednesday']['events'][1]['day_type']);
        $this->assertArraySubset((array)$event2, $dataRow['Thursday']['events'][1]);
        $this->assertFalse(isset($dataRow['Thursday']['events'][1]['day_type']));
        $this->assertArraySubset((array)$event2, $dataRow['Friday']['events'][1]);
        $this->assertEquals('last', $dataRow['Friday']['events'][1]['day_type']);
    }
}

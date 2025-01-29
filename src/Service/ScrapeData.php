<?php

namespace App\Service;

use App\Model\Lesson;
use App\Model\Subject;
use App\Model\Worker;
use App\Model\ClassGroup;
use App\Model\Room;
use App\Model\Faculty;

class ScrapeData
{
    public function fetchData(string $faculty, string $start, string $end)
    {
        $url = "https://plan.zut.edu.pl/schedule_student.php?kind=apiwi&department={$faculty}&start={$start}&end={$end}";
        $data = file_get_contents($url);
        $data = json_decode($data, true);
        foreach ($data as $object) {
            $this->addData($object);
        }
    }

    private function addData(array $object)
    {
        if (isset($object['wydzial'])) {
            $faculty = Faculty::fromApi($object);
            $faculty->save();
        }
        if (isset($object['group_name'])) {
            $group = ClassGroup::fromApi($object);
            $group->save();
        }

        if (isset($object['room'])) {
            $room = Room::fromApi($object);
            $room->save();
        }

        if (isset($object['worker'])) {
            $worker = Worker::fromApi($object);
            $worker->save();
        }

        if (isset($object['title'])) {
            $subject = Subject::fromApi($object);
            $subject->save();
        }

        if (isset($object['description'])) {
            $lesson = Lesson::fromApi($object);
            $lesson->save();
        }
    }
}
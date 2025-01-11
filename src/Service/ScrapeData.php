<?php

namespace App\Service;

use App\Model\Lesson;
use App\Model\Subject;
use App\Model\Worker;
use App\Model\ClassGroup;
use App\Model\Student;
use App\Model\Room;
use App\Model\Faculty;
use App\Model\StudentGroup;

class ScrapeData
{
    public function fatchData(string $faculty, string $start, string $end)
    {
        $url = "https://plan.zut.edu.pl/schedule_student.php?kind=apiwi&department={$faculty}&start={$start}&end={$end}";
        $data = file_get_contents($url);
        $data = json_decode($data, true);
        echo "Data fetched" . PHP_EOL;
        foreach ($data as $object) {
            $this->addData($object);
        }
    }

    private function addData(array $object)
    {
        if (isset($object['wydzial'])) {
        $facultyName = Faculty::fromApi($object);
        $facultyName->save($facultyName->getFacultyName(), $facultyName->getFacultyShort());
        echo "Faculty added" . PHP_EOL;
        }
//        if (isset($object['group_name'])) {
//            $groupName = ClassGroup::fromApi($object);
//            $groupName->save($groupName->getGroupId(), $groupName->getFacultyId(), $groupName->getSemester(), $groupName->getDepartment(), $groupName->getFieldOfStudy());
//        }
//
//        if (isset($object['room_name'])) {
//            $roomName = Room::fromApi($object);
//            $roomName->save($roomName->getRoomId(), $roomName->getFacultyId());
//        }
//
//        if (isset($object['worker_name'])) {
//            $workerName = Worker::fromApi($object);
//            $workerName->save($workerName->getWorkerId());
//        }
//
//        if (isset($object['subject_name'])) {
//            $subjectName = Subject::fromApi($object);
//            $subjectName->save($subjectName->getSubjectId());
//        }
//
//        if (isset($object['lesson_id'])) {
//            $lessonId = Lesson::fromApi($object);
//            $lessonId->save($lessonId->getLessonId(), $lessonId->getSubjectId(), $lessonId->getWorkerId(), $lessonId->getGroupId(), $lessonId->getRoomId());
//        }
//        if (isset($object['student_id'])) {
//            $studentId = Student::fromApi($object);
//            $studentId->save($studentId->getStudentId());
//        }
//
//        if (isset($object['student_id']) && isset($object['group_id'])) {
//            $studentGroup = StudentGroup::fromApi($object);
//            $studentGroup->save($studentGroup->getStudentId(), $studentGroup->getGroupId());
//        }
    }
}
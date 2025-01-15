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
        foreach ($data as $object) {
            $this->addData($object);
        }
    }

    private function addData(array $object)
    {
        if (isset($object['wydzial'])) {
            $faculty = Faculty::fromApi($object);
            $faculty->save($faculty->getFacultyName(), $faculty->getFacultyShort());
        }
        if (isset($object['group_name'])) {
            $group = ClassGroup::fromApi($object);
            $group->save($group->getGroupName(),$group->getYear(), $group->getSemester(), $group->getFacultyId(), $group->getFaculty(), $group->getFieldOfStudy(), $group->getTypeOfStudy());
        }

        if (isset($object['room'])) {
            $room = Room::fromApi($object);
            $room->save($room->getRoomName(), $room->getFacultyId());
        }

        if (isset($object['worker'])) {
            $worker = Worker::fromApi($object);
            $worker->save($worker->getTitle(), $worker->getFirstName(), $worker->getLastName(), $worker->getFullName(), $worker->getLogin(), $worker->getFacultyId());
        }

        if (isset($object['title'])) {
            $subject = Subject::fromApi($object);
            $subject->save($subject->getSubjectName(), $subject->getSubjectType(), $subject->getFacultyId());
        }

        if (isset($object['description'])) {
            $lesson = Lesson::fromApi($object);
            $lesson->save($lesson->getSubjectId(), $lesson->getWorkerId(), $lesson->getGroupId(), $lesson->getRoomId(),$lesson->getLessonDescription(), $lesson->getLessonForm(), $lesson->getLessonFormShort(), $lesson->getLessonStatus(), $lesson->getLessonStart(), $lesson->getLessonEnd());
        }
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
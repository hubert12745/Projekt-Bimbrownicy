<?php

namespace App\Model;

use App\Service\Config;

class Student
{
    private ?int $studentId;

    public function getStudentId(): ?int
    {
        return $this->studentId;
    }

    public function setStudentId(?int $studentId): Student
    {
        $this->studentId = $studentId;
        return $this;
    }

    public static function checkAndInsertStudent($studentId)
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('SELECT student_id FROM Student WHERE student_id = :student_id');
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($student === false) {
            $apiUrl =         $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?number={$studentId}&start=2025-01-20&end=2025-01-27";
            $apiResponse = file_get_contents($apiUrl);
            $studentData = json_decode($apiResponse, true);
            foreach ($studentData as $object) {
                $object['student_id'] = $studentId;
                $student = Student::fromApi($object);
                $student->save();
                if(isset($object['group_name'])){
                    $studentGroup = StudentGroup::fromApi($object);
                    $studentGroup->save();
                }
            }
        }else{
            error_log("Student with ID: $studentId already exists in the database");
        }
    }
    public function fill($array): Student
    {
        $this->setStudentId($array['student_id']);

        return $this;
    }

    public static function fromApi($array): Student
    {
        $student = new self();
        $student->fill($array);
        return $student;
    }

    public function save()
    {
        $pdo  = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO Student(student_id) VALUES (:student_id)');
        $stmt->execute([
            'student_id' => $this->studentId
        ]);
    }
}
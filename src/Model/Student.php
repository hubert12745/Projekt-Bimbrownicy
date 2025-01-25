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
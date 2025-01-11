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
        foreach ($array as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

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
        $stmt = $pdo->prepare('INSERT INTO Student(student_id) VALUES (:student_id)');
        $stmt->execute([
            'student_id' => $this->studentId
        ]);
    }
}
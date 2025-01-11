<?php

namespace App\Model;

use App\Service\Config;

class StudentGroup
{
    private ?int $studentId;
    private ?int $groupId;

    public function getStudentId(): ?int
    {
        return $this->studentId;
    }

    public function setStudentId(?int $studentId): StudentGroup
    {
        $this->studentId = $studentId;
        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(?int $groupId): StudentGroup
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function fill($array): Faculty
    {
        foreach ($array as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public static function fromApi($array): Faculty
    {
        $faculty = new self();
        $faculty->fill($array);

        return $faculty;
    }

    public function save($studentId, $groupId)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO StudentGroup (student_id, group_id) VALUES (:student_id, :group_id)');
        $stmt->execute([
            'student_id' => $studentId,
            'group_id' => $groupId
        ]);

    }
}
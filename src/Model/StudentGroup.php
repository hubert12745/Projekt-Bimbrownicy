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
    private function findId(string $groupName): ?int{
        $pdo = new \PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );
        $stmt = $pdo->prepare('SELECT group_id FROM ClassGroup WHERE group_name = :group_name');
        $stmt->execute(['group_name' => $groupName]);
        $groupId = $stmt->fetchColumn();
        if ($groupId === false) {
            return null;
        }
        return (int)$groupId;
    }
    public function fill($array): StudentGroup
    {

        $this->setStudentId($array['student_id']);
        $this->setGroupId($this->findId($array['group_name'] ?? ''));

        return $this;
    }

    public static function fromApi($array): StudentGroup
    {
        $faculty = new self();
        $faculty->fill($array);

        return $faculty;
    }

    public function save()
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT OR REPLACE INTO StudentGroup (student_id, group_id) VALUES (:student_id, :group_id)');
        $stmt->execute([
            'student_id' => $this->studentId,
            'group_id' => $this->groupId
        ]);

    }
}
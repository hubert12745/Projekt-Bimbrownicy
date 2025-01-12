<?php

namespace App\Model;

use App\Service\Config;
use PDO;
class Subject
{
    private ?int $subjectId;
    private ?string $subjectName;
    private ?string $subjectType;
    private ?int $facultyId;

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function setSubjectId(?int $subjectId): Subject
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getSubjectName(): ?string
    {
        return $this->subjectName;
    }

    public function setSubjectName(?string $subjectName): Subject
    {
        $this->subjectName = $subjectName;
        return $this;
    }

    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    public function setSubjectType(?string $subjectType): Subject
    {
        $this->subjectType = $subjectType;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): Subject
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    private function findForeignKeys($facultyShort): int
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('SELECT faculty_id FROM Faculty WHERE faculty_short = :faculty_short');
        return $stmt->execute(['faculty_short' => $facultyShort]);
    }
    public function fill($array): Subject
    {

        $this->setSubjectName($array['title']);
        $this->setSubjectType($array['lesson_form']);
        $this->setFacultyId($this->findForeignKeys($array['wydz_sk']));

        return $this;
    }

    public static function fromApi($array): Subject
    {
        $faculty = new self();
        $faculty->fill($array);

        return $faculty;
    }

    public function save($subjectName, $subjectType, $facultyId)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO Subject (subject_name, subject_type, faculty_id) VALUES ( :subject_name, :subject_type, :faculty_id)');
        $stmt->execute([
            'subject_name' => $subjectName,
            'subject_type' => $subjectType,
            'faculty_id' => $facultyId
        ]);
    }


}
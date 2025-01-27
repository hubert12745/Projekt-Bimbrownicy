<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class Subject
{
    private ?int $subjectId   = null;
    private ?string $subjectName = null;
    private ?string $subjectType = null;
    private ?int $facultyId   = null;

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

    private function findForeignKeys(string $facultyShort): ?int
    {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );
        $stmt = $pdo->prepare(
            'SELECT faculty_id FROM Faculty WHERE faculty_short = :faculty_short'
        );

        $stmt->execute(['faculty_short' => $facultyShort]);
        $facultyId = $stmt->fetchColumn();

        if ($facultyId === false) {
            return null;
        }

        return (int) $facultyId;
    }

    public function fill(array $array): Subject
    {
        $this->setSubjectName($array['title'] ?? null);
        $this->setSubjectType($array['lesson_form'] ?? null);

        $facultyId = $this->findForeignKeys($array['wydz_sk'] ?? '');
        $this->setFacultyId($facultyId);

        return $this;
    }

    public static function fromApi(array $array): Subject
    {
        $subject = new self();
        $subject->fill($array);
        return $subject;
    }

    public function save(): void
    {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            'INSERT OR REPLACE INTO Subject (subject_name, subject_type, faculty_id)
             VALUES (:subject_name, :subject_type, :faculty_id)'
        );

        $stmt->execute([
            'subject_name' => $this->subjectName,
            'subject_type' => $this->subjectType,
            'faculty_id'   => $this->facultyId
        ]);
    }
}

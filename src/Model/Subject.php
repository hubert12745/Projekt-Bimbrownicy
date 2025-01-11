<?php

namespace App\Model;

use App\Service\Config;

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

    public function save($subjectId, $subjectName, $subjectType, $facultyId)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO subjects (subject_id, subject_name, subject_type, faculty_id) VALUES (:subject_id, :subject_name, :subject_type, :faculty_id)');
        $stmt->execute([
            'subject_name' => $subjectName,
            'subject_type' => $subjectType,
            'faculty_id' => $facultyId
        ]);
    }


}
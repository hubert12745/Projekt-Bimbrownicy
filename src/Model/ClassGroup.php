<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class ClassGroup
{
    private ?int $groupId     = null;
    private ?string $groupName  = null;
    private ?int $semester    = null;
    private ?int $facultyId   = null;
    private ?string $faculty  = null;
    private ?string $fieldOfStudy = null;

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(?int $groupId): ClassGroup
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): ClassGroup
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getSemester(): ?int
    {
        return $this->semester;
    }

    public function setSemester(?int $semester): ClassGroup
    {
        $this->semester = $semester;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): ClassGroup
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function getFaculty(): ?string
    {
        return $this->faculty;
    }

    public function setFaculty(?string $faculty): ClassGroup
    {
        $this->faculty = $faculty;
        return $this;
    }

    public function getFieldOfStudy(): ?string
    {
        return $this->fieldOfStudy;
    }

    public function setFieldOfStudy(?string $fieldOfStudy): ClassGroup
    {
        $this->fieldOfStudy = $fieldOfStudy;
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

        return (int)$facultyId;
    }

    public function fill(array $array): ClassGroup
    {
        $this->setGroupName($array['group_name'] ?? null);
        $this->setSemester($array['semestr'] ?? null);

        $facultyId = $this->findForeignKeys($array['wydz_sk'] ?? '');
        $this->setFacultyId($facultyId);

        $this->setFaculty($array['wydzial'] ?? null);
        $this->setFieldOfStudy($array['kierunek'] ?? null);

        return $this;
    }

    public static function fromApi(array $array): ClassGroup
    {
        $classGroup = new self();
        $classGroup->fill($array);

        return $classGroup;
    }

    public function save(
        ?string $groupName,
        ?int $semester,
        ?int $facultyId,
        ?string $faculty,
        ?string $fieldOfStudy
    ): void {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO ClassGroup 
               (group_name, semester, faculty_id, department, field_of_study) 
             VALUES 
               (:group_name, :semester, :faculty_id, :department, :field_of_study)'
        );

        $stmt->execute([
            'group_name'     => $groupName,
            'semester'       => $semester,
            'faculty_id'     => $facultyId,
            'department'     => $faculty,
            'field_of_study' => $fieldOfStudy,
        ]);
    }
}

<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class ClassGroup
{
    private ?int $groupId = null;
    private ?string $groupName = null;
    private ?int $year = null;
    private ?int $semester = null;
    private ?int $facultyId = null;
    private ?string $faculty = null;
    private ?string $fieldOfStudy = null;

    private ?string $typeOfStudy = null;

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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): ClassGroup
    {
        $this->year = $year;
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

    public function getTypeOfStudy(): ?string
    {
        return $this->typeOfStudy;
    }

    public function setTypeOfStudy(?string $typeOfStudy): ClassGroup
    {
        $this->typeOfStudy = $typeOfStudy;
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
        $this->setYear(ceil($array['semestr'] / 2) ?? null);
        $this->setSemester($array['semestr'] ?? null);

        $facultyId = $this->findForeignKeys($array['wydz_sk'] ?? '');
        $this->setFacultyId($facultyId);

        $this->setFaculty($array['wydzial'] ?? null);
        $this->setFieldOfStudy($array['kierunek'] ?? null);
        $this->setTypeOfStudy($array['rodzaj'] ?? null);

        return $this;
    }

    public static function fromApi(array $array): ClassGroup
    {
        $classGroup = new self();
        $classGroup->fill($array);

        return $classGroup;
    }

    public function save(
//        ?string $groupName,
//        ?int    $year,
//        ?int    $semester,
//        ?int    $facultyId,
//        ?string $faculty,
//        ?string $fieldOfStudy,
//        ?string $typeOfStudy
    ): void
    {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO ClassGroup 
               (group_name,year, semester, faculty_id, department, field_of_study, type_of_study) 
             VALUES 
               (:group_name,:year, :semester, :faculty_id, :department, :field_of_study, :type_of_study)'
        );

        $stmt->execute([
            'group_name' => $this->groupName,
            'year' => $this->year,
            'semester' => $this->semester,
            'faculty_id' => $this->facultyId,
            'department' => $this->faculty,
            'field_of_study' => $this->fieldOfStudy,
            'type_of_study' => $this->typeOfStudy
        ]);
    }
}

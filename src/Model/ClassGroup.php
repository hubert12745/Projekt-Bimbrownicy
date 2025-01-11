<?php

namespace App\Model;

use App\Service\Config;

class ClassGroup
{
    private ?int $groupId;
    private ?string $groupName;
    private ?int $semester;
    private ?int $facultyId;
    private ?string $department;
    private ?string $fieldOfStudy;

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

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): ClassGroup
    {
        $this->department = $department;
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

    public function fill($array): ClassGroup
    {
        foreach ($array as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
    public static function fromApi($array): ClassGroup
    {
        $classGroup = new self();
        $classGroup->fill($array);

        return $classGroup;
    }
    public function save($groupId, $groupName, $semester, $facultyId, $department, $fieldOfStudy)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO ClassGroup (group_id, group_name, semester, faculty_id, department, field_of_study) VALUES (:group_id, :group_name, :semester, :faculty_id, :department, :field_of_study)');
        $stmt->execute([
            'group_name' => $groupName,
            'semester' => $semester,
            'faculty_id' => $facultyId,
            'department' => $department,
            'field_of_study' => $fieldOfStudy
        ]);
    }
}
<?php

namespace App\Model;

class Faculty
{
    private ?int $facultyId;
    private ?string $facultyName;
    private ?string $facultyShort;

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): Faculty
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function getFacultyName(): ?string
    {
        return $this->facultyName;
    }

    public function setFacultyName(?string $facultyName): Faculty
    {
        $this->facultyName = $facultyName;
        return $this;
    }

    public function getFacultyShort(): ?string
    {
        return $this->facultyShort;
    }

    public function setFacultyShort(?string $facultyShort): Faculty
    {
        $this->facultyShort = $facultyShort;
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

    public function save($facultyId, $facultyName, $facultyShort)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO Faculty (faculty_id, faculty_name, faculty_short) VALUES (:faculty_id, :faculty_name, :faculty_short)');
        $stmt->execute([
            'faculty_id' => $facultyId,
            'faculty_name' => $facultyName,
            'faculty_short' => $facultyShort
        ]);
    }
}
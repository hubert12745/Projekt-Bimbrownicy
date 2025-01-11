<?php

namespace App\Model;

use App\Service\Config;

class Worker
{
    private ?int $workerId;
    private ?string $title;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $fullName;
    private ?string $login;
    private ?int $facultyId;

    public function getWorkerId(): ?int
    {
        return $this->workerId;
    }

    public function setWorkerId(?int $workerId): Worker
    {
        $this->workerId = $workerId;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(?string $title): Worker
    {
        $this->title = $title;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): Worker
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): Worker
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): Worker
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): Worker
    {
        $this->login = $login;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): Worker
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

    public function save($title, $firstName, $lastName, $fullName, $login, $facultyId)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO Worker (title, first_name, last_name, full_name, login, faculty_id) VALUES (:worker_id, :title, :first_name, :last_name, :full_name, :login, :faculty_id)');
        $stmt->execute([
            'title' => $title,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'login' => $login,
            'faculty_id' => $facultyId
        ]);

    }


}
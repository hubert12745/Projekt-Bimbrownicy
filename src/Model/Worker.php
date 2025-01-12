<?php

namespace App\Model;

use App\Service\Config;
use PDO;
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

    private function findForeignKeys($facultyShort): int
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('SELECT faculty_id FROM Faculty WHERE faculty_short = :faculty_short');
        return $stmt->execute(['faculty_short' => $facultyShort]);
    }
    public function fill($array): Worker
    {
        $this ->setTitle($array['tytul']);
        $this ->setFirstName($array['imie']);
        $this ->setLastName($array['nazwisko']);
        $this ->setFullName($array['worker']);
        $this ->setLogin($array['login']);
        $this ->setFacultyId($this->findForeignKeys($array['wydz_sk']));

        return $this;
    }

    public static function fromApi($array): Worker
    {
        $faculty = new self();
        $faculty->fill($array);

        return $faculty;
    }

    public function save($title, $firstName, $lastName, $fullName, $login, $facultyId)
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO Worker (title, first_name, last_name, full_name, login, faculty_id) VALUES ( :title, :first_name, :last_name, :full_name, :login, :faculty_id)');
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
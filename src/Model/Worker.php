<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class Worker
{
    private ?int $workerId   = null;
    private ?string $title   = null;
    private ?string $firstName = null;
    private ?string $lastName  = null;
    private ?string $fullName  = null;
    private ?string $login     = null;
    private ?int $facultyId  = null;

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

    public function fill(array $array): Worker
    {
        $this->setTitle($array['tytul'] ?? null);
        $this->setFirstName($array['imie'] ?? null);
        $this->setLastName($array['nazwisko'] ?? null);
        $this->setFullName($array['worker'] ?? null);
        $this->setLogin($array['login'] ?? null);

        $facultyId = $this->findForeignKeys($array['wydz_sk'] ?? '');
        $this->setFacultyId($facultyId);

        return $this;
    }
    public static function fromApi(array $array): Worker
    {
        $worker = new self();
        $worker->fill($array);
        return $worker;
    }

    public function save(
        ?string $title,
        ?string $firstName,
        ?string $lastName,
        ?string $fullName,
        ?string $login,
        ?int $facultyId
    ): void {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO Worker 
            (title, first_name, last_name, full_name, login, faculty_id)
            VALUES
            (:title, :first_name, :last_name, :full_name, :login, :faculty_id)'
        );

        $stmt->execute([
            'title'      => $title,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'full_name'  => $fullName,
            'login'      => $login,
            'faculty_id' => $facultyId
        ]);
    }
}

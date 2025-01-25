<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class Room
{
    private ?int $room_id   = null;
    private ?string $room_name  = null;
    private ?int $faculty_id = null;

    public function getRoomId(): ?int
    {
        return $this->room_id;
    }

    public function setRoomId(?int $room_id): Room
    {
        $this->room_id = $room_id;
        return $this;
    }

    public function getRoomName(): ?string
    {
        return $this->room_name;
    }

    public function setRoomName(?string $room_name): Room
    {
        $this->room_name = $room_name;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->faculty_id;
    }

    public function setFacultyId(?int $faculty_id): Room
    {
        $this->faculty_id = $faculty_id;
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


    public function fill(array $array): Room
    {
        $this->setRoomName($array['room'] ?? null);

        $facultyId = $this->findForeignKeys($array['wydz_sk'] ?? '');
        $this->setFacultyId($facultyId);

        return $this;
    }

    public static function fromApi(array $array): Room
    {
        $room = new self();
        $room->fill($array);
        return $room;
    }


    public function save(): void
    {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO Room (room_name, faculty_id)
             VALUES (:room_name, :faculty_id)'
        );

        $stmt->execute([
            'room_name'  => $this->room_name,
            'faculty_id' => $this->faculty_id
        ]);
    }
}

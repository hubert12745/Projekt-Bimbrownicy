<?php

namespace App\Model;

use App\Service\Config;

class Room
{
    private ?int $room_id;
    private ?string $room_name;
    private ?int $faculty_id;

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

    public function fill($array): Room
    {
        foreach ($array as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public static function fromApi($array): Room
    {
        $room = new self();
        $room->fill($array);
        return $room;
    }

    public function save( $room_name, $faculty_id)
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT INTO room ( room_name, faculty_id) VALUES (:room_id, :room_name, :faculty_id)');
        $stmt->execute([
            'room_name' => $room_name,
            'faculty_id' => $faculty_id
        ]);
    }

}
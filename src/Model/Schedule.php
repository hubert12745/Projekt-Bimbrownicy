<?php
namespace App\Model;

use App\Service\Config;
use PDO;

class Schedule
{
    private ?int $id = null;
    private ?string $data_start = null;
    private ?string $data_koniec = null;
    private ?string $lecturer = null;
    private ?string $department = null;
    private ?string $group = null;
    private ?string $study_track = null;
    private ?string $subject = null;
    private ?string $room = null;
    private ?int $semester = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Schedule
    {
        $this->id = $id;
        return $this;
    }

    public function getDataStart(): ?string
    {
        return $this->data_start;
    }

    public function setDataStart(?string $data_start): Schedule
    {
        $this->data_start = $data_start;
        return $this;
    }

    public function getDataKoniec(): ?string
    {
        return $this->data_koniec;
    }

    public function setDataKoniec(?string $data_koniec): Schedule
    {
        $this->data_koniec = $data_koniec;
        return $this;
    }

    public function getLecturer(): ?string
    {
        return $this->lecturer;
    }

    public function setLecturer(?string $lecturer): Schedule
    {
        $this->lecturer = $lecturer;
        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): Schedule
    {
        $this->department = $department;
        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): Schedule
    {
        $this->group = $group;
        return $this;
    }

    public function getStudyTrack(): ?string
    {
        return $this->study_track;
    }

    public function setStudyTrack(?string $study_track): Schedule
    {
        $this->study_track = $study_track;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): Schedule
    {
        $this->subject = $subject;
        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(?string $room): Schedule
    {
        $this->room = $room;
        return $this;
    }

    public function getSemester(): ?int
    {
        return $this->semester;
    }

    public function setSemester(?int $semester): Schedule
    {
        $this->semester = $semester;
        return $this;
    }

    public static function fromArray($array): Schedule
    {
        $schedule = new self();
        $schedule->fill($array);
        return $schedule;
    }

    public function fill($array): Schedule
    {
        if (isset($array['id']) && !$this->getId()) {
            $this->setId($array['id']);
        }
        if (isset($array['data_start'])) {
            $this->setDataStart($array['data_start']);
        }
        if (isset($array['data_koniec'])) {
            $this->setDataKoniec($array['data_koniec']);
        }
        if (isset($array['lecturer'])) {
            $this->setLecturer($array['lecturer']);
        }
        if (isset($array['department'])) {
            $this->setDepartment($array['department']);
        }
        if (isset($array['group'])) {
            $this->setGroup($array['group']);
        }
        if (isset($array['study_track'])) {
            $this->setStudyTrack($array['study_track']);
        }
        if (isset($array['subject'])) {
            $this->setSubject($array['subject']);
        }
        if (isset($array['room'])) {
            $this->setRoom($array['room']);
        }
        if (isset($array['semester'])) {
            $this->setSemester($array['semester']);
        }
        return $this;
    }

    public static function findAll(): array
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $sql = 'SELECT * FROM schedule';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $schedules = [];
        $schedulesArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($schedulesArray as $scheduleArray) {
            $schedules[] = self::fromArray($scheduleArray);
        }

        return $schedules;
    }

    public static function find($id): ?Schedule
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $sql = 'SELECT * FROM schedule WHERE id = :id';
        $statement = $pdo->prepare($sql);
        $statement->execute(['id' => $id]);

        $scheduleArray = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$scheduleArray) {
            return null;
        }
        $schedule = Schedule::fromArray($scheduleArray);

        return $schedule;
    }

    public function save(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (!$this->getId()) {
            $sql = "INSERT INTO schedule (data_start, data_koniec, lecturer, department, `group`, study_track, subject, room, semester) VALUES (:data_start, :data_koniec, :lecturer, :department, :group, :study_track, :subject, :room, :semester)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'data_start' => $this->getDataStart(),
                'data_koniec' => $this->getDataKoniec(),
                'lecturer' => $this->getLecturer(),
                'department' => $this->getDepartment(),
                'group' => $this->getGroup(),
                'study_track' => $this->getStudyTrack(),
                'subject' => $this->getSubject(),
                'room' => $this->getRoom(),
                'semester' => $this->getSemester(),
            ]);

            $this->setId($pdo->lastInsertId());
        } else {
            $sql = "UPDATE schedule SET data_start = :data_start, data_koniec = :data_koniec, lecturer = :lecturer, department = :department, `group` = :group, study_track = :study_track, subject = :subject, room = :room, semester = :semester WHERE id = :id";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                ':data_start' => $this->getDataStart(),
                ':data_koniec' => $this->getDataKoniec(),
                ':lecturer' => $this->getLecturer(),
                ':department' => $this->getDepartment(),
                ':group' => $this->getGroup(),
                ':study_track' => $this->getStudyTrack(),
                ':subject' => $this->getSubject(),
                ':room' => $this->getRoom(),
                ':semester' => $this->getSemester(),
                ':id' => $this->getId(),
            ]);
        }
    }

    public function delete(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $sql = "DELETE FROM schedule WHERE id = :id";
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':id' => $this->getId(),
        ]);

        $this->setId(null);
        $this->setDataStart(null);
        $this->setDataKoniec(null);
        $this->setLecturer(null);
        $this->setDepartment(null);
        $this->setGroup(null);
        $this->setStudyTrack(null);
        $this->setSubject(null);
        $this->setRoom(null);
        $this->setSemester(null);
    }
}
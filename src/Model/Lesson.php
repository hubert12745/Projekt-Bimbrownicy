<?php

namespace App\Model;

use App\Service\Config;
use PDO;

class Lesson
{
    private ?int $lesson_id;
    private ?int $subject_id;
    private ?int $worker_id;
    private ?int $group_id;
    private ?int $room_id;
    private ?string $lesson_description;
    private ?string $lesson_form;
    private ?string $lesson_form_short;
    private ?string $lesson_status;
    private ?string $lesson_status_short;
    private ?string $lesson_start;
    private ?string $lesson_end;

    public function getLessonId(): ?int
    {
        return $this->lesson_id;
    }

    public function setLessonId(?int $lesson_id): Lesson
    {
        $this->lesson_id = $lesson_id;
        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subject_id;
    }

    public function setSubjectId(?int $subject_id): Lesson
    {
        $this->subject_id = $subject_id;
        return $this;
    }

    public function getWorkerId(): ?int
    {
        return $this->worker_id;
    }

    public function setWorkerId(?int $worker_id): Lesson
    {
        $this->worker_id = $worker_id;
        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    public function setGroupId(?int $group_id): Lesson
    {
        $this->group_id = $group_id;
        return $this;
    }

    public function getRoomId(): ?int
    {
        return $this->room_id;
    }

    public function setRoomId(?int $room_id): Lesson
    {
        $this->room_id = $room_id;
        return $this;
    }

    public function getLessonDescription(): ?string
    {
        return $this->lesson_description;
    }

    public function setLessonDescription(?string $lesson_description): Lesson
    {
        $this->lesson_description = $lesson_description;
        return $this;
    }

    public function getLessonForm(): ?string
    {
        return $this->lesson_form;
    }

    public function setLessonForm(?string $lesson_form): Lesson
    {
        $this->lesson_form = $lesson_form;
        return $this;
    }

    public function getLessonFormShort(): ?string
    {
        return $this->lesson_form_short;
    }

    public function setLessonFormShort(?string $lesson_form_short): Lesson
    {
        $this->lesson_form_short = $lesson_form_short;
        return $this;
    }

    public function getLessonStatus(): ?string
    {
        return $this->lesson_status;
    }

    public function setLessonStatus(?string $lesson_status): Lesson
    {
        $this->lesson_status = $lesson_status;
        return $this;
    }

    public function getLessonStatusShort(): ?string
    {
        return $this->lesson_status_short;
    }

    public function setLessonStatusShort(?string $lesson_status_short): Lesson
    {
        $this->lesson_status_short = $lesson_status_short;
        return $this;
    }

    public function getLessonStart(): ?string
    {
        return $this->lesson_start;
    }

    public function setLessonStart(?string $lesson_start): Lesson
    {
        $this->lesson_start = $lesson_start;
        return $this;
    }

    public function getLessonEnd(): ?string
    {
        return $this->lesson_end;
    }

    public function setLessonEnd(?string $lesson_end): Lesson
    {
        $this->lesson_end = $lesson_end;
        return $this;
    }

    private function findId(
        string $tableName,
        string $whereColumn,
        ?string $value,
        string $idColumn
    ): ?int {
        if ($value === null) {
            return null;
        }
        $pdo = new \PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );


        $sql = "SELECT {$idColumn} FROM {$tableName} WHERE {$whereColumn} = :val";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['val' => $value]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return (int)$row[$idColumn];
    }

    public function fill(array $array): Lesson
    {
        $this->setSubjectId(
            $this->findId('Subject', 'subject_name', $array['title'], 'subject_id')
        );
        $this->setWorkerId(
            $this->findId('Worker', 'login', $array['login'], 'worker_id')
        );
        $this->setGroupId(
            $this->findId('ClassGroup', 'group_name', $array['group_name'], 'group_id')
        );
        $this->setRoomId(
            $this->findId('Room', 'room_name', $array['room'], 'room_id')
        );

        $this->setLessonDescription($array['description']);
        $this->setLessonForm($array['lesson_form']);
        $this->setLessonFormShort($array['lesson_form_short']);
        $this->setLessonStatus($array['status_item']);
        $this->setLessonStart($array['start']);
        $this->setLessonEnd($array['end']);

        return $this;
    }

    public static function fromApi(array $array): Lesson
    {
        $lesson = new self();
        $lesson->fill($array);
        return $lesson;
    }

    public function save(): void {
        $pdo = new \PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );

        $stmt = $pdo->prepare(
            "INSERT OR IGNORE INTO Lesson (
                subject_id, 
                worker_id, 
                group_id, 
                room_id, 
                lesson_description, 
                lesson_form,
                lesson_form_short, 
                lesson_status, 
                lesson_start, 
                lesson_end
            ) VALUES (
                :subject_id, 
                :worker_id, 
                :group_id, 
                :room_id, 
                :lesson_description, 
                :lesson_form, 
                :lesson_form_short, 
                :lesson_status, 
                :lesson_start, 
                :lesson_end
            )"
        );

        $stmt->execute([
            'subject_id'         => $this->subject_id,
            'worker_id'          => $this->worker_id,
            'group_id'           => $this->group_id,
            'room_id'            => $this->room_id,
            'lesson_description' => $this->lesson_description,
            'lesson_form'        => $this->lesson_form,
            'lesson_form_short'  => $this->lesson_form_short,
            'lesson_status'      => $this->lesson_status,
            'lesson_start'       => $this->lesson_start,
            'lesson_end'         => $this->lesson_end
        ]);
    }
}

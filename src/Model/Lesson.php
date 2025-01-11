<?php

namespace App\Model;

use App\Service\Config;

class Lesson
{
    private ?int $lesson_id;
    private ?int $subject_id;
    private ?int $worker_id;
    private ?int $group_id;
    private ?int $room_id;
    private?string $lesson_form;
    private?string $lesson_form_short;
    private?string $lesson_status;
    private?string $lesson_status_short;
    private?string $lesson_start;
    private?string $lesson_end;

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

    public function fill($array): Lesson
    {
        foreach ($array as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public static function fromApi($array): Lesson
    {
        $lesson = new self();
        $lesson->fill($array);
        return $lesson;
    }

    public function save( $subject_id, $worker_id, $group_id, $room_id, $lesson_form, $lesson_form_short, $lesson_status, $lesson_status_short, $lesson_start, $lesson_end)
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare("INSERT INTO Lesson ( subject_id, worker_id, group_id, room_id, lesson_form, lesson_form_short, lesson_status, lesson_status_short, lesson_start, lesson_end) VALUES (:lesson_id, :subject_id, :worker_id, :group_id, :room_id, :lesson_form, :lesson_form_short, :lesson_status, :lesson_status_short, :lesson_start, :lesson_end)");
        $stmt->execute([
            'subject_id' => $subject_id,
            'worker_id' => $worker_id,
            'group_id' => $group_id,
            'room_id' => $room_id,
            'lesson_form' => $lesson_form,
            'lesson_form_short' => $lesson_form_short,
            'lesson_status' => $lesson_status,
            'lesson_status_short' => $lesson_status_short,
            'lesson_start' => $lesson_start,
            'lesson_end' => $lesson_end
        ]);
    }


}
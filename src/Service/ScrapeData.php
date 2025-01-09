<?php
namespace App\Service;

use PDO;
use PDOException;

class ScrapeData
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, 10); // Set busy timeout to 10 seconds
    }

    public function fetchData(string $department, string $start, string $end)
    {
        $apiURL = "https://plan.zut.edu.pl/schedule_student.php?kind=apiwi&department={$department}&start={$start}&end={$end}";
        $response = file_get_contents($apiURL);
        if (!$response) {
            die('No response');
        }

        $data = json_decode($response, true);

        try {
            $this->pdo->beginTransaction();
            foreach ($data as $item) {
                $this->processItem($item);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function processItem(array $item)
    {
        if (isset($item['wydzial']) && isset($item['wydz_sk'])) {
            $this->addFaculty($item['wydzial'], $item['wydz_sk']);
        }
        if (isset($item['worker'])) {
            $this->addWorker($item['worker'], $item['tytul'] ?? 'Brak');
        }

        if (isset($item['room'])) {
            $this->addRoom($item['room'], $item['wydzial']);
        }

        if (isset($item['group_name'])) {
            $this->addClassGroup($item['group_name'], $item['semestr'] ?? 0, $item['wydzial'], $item['field_of_study'] ?? 'Brak');
        }

        if (isset($item['subject'])) {
            $this->addSubject($item['subject'], $item['typ_sk'] ?? 'Brak', $item['wydzial']);
        }

        if (isset($item['id'])) {
            $this->addLesson(
                $item['id'],
                $item['start'],
                $item['end'],
                $item['worker'],
                $item['group_name'],
                $item['subject'],
                $item['lesson_form'] ?? 'Brak',
                $item['lesson_form_short'] ?? 'Brak',
                $item['lesson_status'] ?? 'Brak',
                $item['lesson_status_short'] ?? 'Brak',
                $item['room']
            );
        }
    }

    private function addFaculty(string $name, string $shortName)
    {
        $faculty = new Department();
        $faculty->setName($name);
        $faculty->setShortName($shortName);
        $faculty->save();
    }

    private function addRoom(string $room, string $faculty)
    {
        $facultyId = $this->getFacultyId($faculty);
        if ($facultyId) {
            $roomWithBuilding = new RoomWithBuilding();
            $roomWithBuilding->setName($room);
            $roomWithBuilding->setFacultyId($facultyId);
            $roomWithBuilding->save();
        } else {
            throw new \Exception("Faculty not found");
        }
    }

    private function addSubject(string $name, string $type, string $faculty)
    {
        $facultyId = $this->getFacultyId($faculty);
        if ($facultyId) {
            $subject = new Subject();
            $subject->setName($name);
            $subject->setType($type);
            $subject->setFacultyId($facultyId);
            $subject->save();
        } else {
            throw new \Exception("Faculty not found");
        }
    }

    private function addClassGroup(string $name, int $semester, string $faculty, string $fieldOfStudy)
    {
        $facultyId = $this->getFacultyId($faculty);
        if ($facultyId) {
            $classGroup = new CourseOfStudy();
            $classGroup->setName($name);
            $classGroup->setType($fieldOfStudy);
            $classGroup->setFacultyId($facultyId);
            $classGroup->save();
        } else {
            throw new \Exception("Faculty not found");
        }
    }

    private function addWorker(string $name, string $title)
    {
        $stmt = $this->pdo->prepare("SELECT worker_id FROM Worker WHERE full_name = :name");
        $stmt->execute([':name' => $name]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Worker (title, first_name, last_name, full_name, login, faculty_id) VALUES (:title, '', '', :name, '', 1)");
            $stmt->execute([':title' => $title, ':name' => $name]);
        }
    }

    private function addLesson(int $id, string $startDate, string $endDate, string $worker, string $group, string $subject, string $form, string $formShort, string $status, string $statusShort, string $room)
    {
        $workerId = $this->getWorkerId($worker);
        $groupId = $this->getGroupId($group);
        $subjectId = $this->getSubjectId($subject);
        $roomId = $this->getRoomId($room);

        if ($workerId && $groupId && $subjectId && $roomId) {
            $stmt = $this->pdo->prepare("SELECT lesson_id FROM Lesson WHERE lesson_id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO Lesson (lesson_id, subject_id, worker_id, group_id, room_id, lesson_form, lesson_form_short, lesson_status, lesson_status_short, lesson_start, lesson_end) VALUES (:id, :subjectId, :workerId, :groupId, :roomId, :form, :formShort, :status, :statusShort, :startDate, :endDate)");
                $stmt->execute([
                    ':id' => $id,
                    ':subjectId' => $subjectId,
                    ':workerId' => $workerId,
                    ':groupId' => $groupId,
                    ':roomId' => $roomId,
                    ':form' => $form,
                    ':formShort' => $formShort,
                    ':status' => $status,
                    ':statusShort' => $statusShort,
                    ':startDate' => $startDate,
                    ':endDate' => $endDate
                ]);
            }
        }
    }

    private function getFacultyId(string $faculty): ?int
    {
        $stmt = $this->pdo->prepare("SELECT faculty_id FROM Faculty WHERE faculty_name = :faculty");
        $stmt->execute([':faculty' => $faculty]);
        return $stmt->fetchColumn();
    }

    private function getWorkerId(string $worker): ?int
    {
        $stmt = $this->pdo->prepare("SELECT worker_id FROM Worker WHERE full_name = :worker");
        $stmt->execute([':worker' => $worker]);
        return $stmt->fetchColumn();
    }

    private function getGroupId(string $group): ?int
    {
        $stmt = $this->pdo->prepare("SELECT group_id FROM ClassGroup WHERE group_name = :group");
        $stmt->execute([':group' => $group]);
        return $stmt->fetchColumn();
    }

    private function getSubjectId(string $subject): ?int
    {
        $stmt = $this->pdo->prepare("SELECT subject_id FROM Subject WHERE subject_name = :subject");
        $stmt->execute([':subject' => $subject]);
        return $stmt->fetchColumn();
    }

    private function getRoomId(string $room): ?int
    {
        $stmt = $this->pdo->prepare("SELECT room_id FROM Room WHERE room_name = :room");
        $stmt->execute([':room' => $room]);
        return $stmt->fetchColumn();
    }
}

class Department
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $shortName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Department
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Department
    {
        $this->name = $name;
        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): Department
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function save(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (! $this->getId()) {
            $sql = "INSERT INTO Faculty (faculty_name, faculty_short) VALUES (:name, :shortName)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'shortName' => $this->getShortName(),
            ]);
            $this->setId($pdo->lastInsertId());
        } else {
            $sql = "UPDATE Faculty SET faculty_name = :name, faculty_short = :shortName WHERE faculty_id = :id";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'shortName' => $this->getShortName(),
                'id' => $this->getId(),
            ]);
        }
    }
}

class RoomWithBuilding
{
    private ?int $id = null;
    private ?string $name = null;
    private ?int $facultyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): RoomWithBuilding
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): RoomWithBuilding
    {
        $this->name = $name;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): RoomWithBuilding
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function save(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (! $this->getId()) {
            $sql = "INSERT INTO Room (room_name, faculty_id) VALUES (:name, :facultyId)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'facultyId' => $this->getFacultyId(),
            ]);
            $this->setId($pdo->lastInsertId());
        } else {
            $sql = "UPDATE Room SET room_name = :name, faculty_id = :facultyId WHERE room_id = :id";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'facultyId' => $this->getFacultyId(),
                'id' => $this->getId(),
            ]);
        }
    }
}

class CourseOfStudy
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?int $facultyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): CourseOfStudy
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CourseOfStudy
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): CourseOfStudy
    {
        $this->type = $type;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): CourseOfStudy
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function save(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (! $this->getId()) {
            $sql = "INSERT INTO Subject (subject_name, subject_type, faculty_id) VALUES (:name, :type, :facultyId)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'type' => $this->getType(),
                'facultyId' => $this->getFacultyId(),
            ]);
            $this->setId($pdo->lastInsertId());
        } else {
            $sql = "UPDATE Subject SET subject_name = :name, subject_type = :type, faculty_id = :facultyId WHERE subject_id = :id";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'type' => $this->getType(),
                'facultyId' => $this->getFacultyId(),
                'id' => $this->getId(),
            ]);
        }
    }
}

class Subject
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?int $facultyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Subject
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Subject
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Subject
    {
        $this->type = $type;
        return $this;
    }

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): Subject
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function save(): void
    {
        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (! $this->getId()) {
            $sql = "INSERT INTO Subject (subject_name, subject_type, faculty_id) VALUES (:name, :type, :facultyId)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'type' => $this->getType(),
                'facultyId' => $this->getFacultyId(),
            ]);
            $this->setId($pdo->lastInsertId());
        } else {
            $sql = "UPDATE Subject SET subject_name = :name, subject_type = :type, faculty_id = :facultyId WHERE subject_id = :id";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'name' => $this->getName(),
                'type' => $this->getType(),
                'facultyId' => $this->getFacultyId(),
                'id' => $this->getId(),
            ]);
        }
    }
}
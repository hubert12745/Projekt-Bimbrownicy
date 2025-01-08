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
    }

    /**
     * @throws \Exception
     */
    public function fetchData(string $department)
    {
        $apiURL = "https://plan.zut.edu.pl/schedule_student.php?kind=apiwi&department={$department}&start=2024-10-7&end=2024-10-8";
        $response = file_get_contents($apiURL);
        if (!$response) {
            die('No response');
        }

        $data = json_decode($response, true);

        foreach ($data as $item) {
            $this->processItem($item);
        }
    }

    private function processItem(array $item)
    {
        if (isset($item['wydzial']) && isset($item['wydz_sk'])) {
            $this->addDepartment($item['wydzial'], $item['wydz_sk']);
        }

        if (isset($item['room'])) {
            $this->addRoom($item['room'], $item['wydzial']);
        }

        $this->addStudyTrack(
            $item['typ_sk'] ?? 'Brak',
            $item['rodzaj_sk'] ?? 'Brak',
            $item['rodzaj'] ?? 'Brak',
            $item['typ'] ?? 'Brak'
        );

        if (isset($item['subject'])) {
            $this->addSubject(
                $item['subject'],
                $item['rodzaj_sk'] ?? 'Brak',
                $item['typ_sk'] ?? 'Brak',
                $item['lesson_form'] ?? 'Brak',
                $item['semestr'] ?? 0,
                $item['rok'] ?? 0
            );
        }

        if (isset($item['group_name'])) {
            $this->addGroup($item['group_name']);
        }

        if (isset($item['worker'])) {
            $this->addLecturer($item['worker'], $item['tytul'] ?? 'Brak');
        }

        if (isset($item['id'])) {
            $this->addClass(
                $item['id'],
                $item['start'],
                $item['end'],
                $item['worker_cover'] ?? 'Brak',
                $item['worker'],
                $item['wydzial'],
                $item['group_name'],
                $item['typ_sk'] ?? 'Brak',
                $item['subject'],
                $item['lesson_form'] ?? 'Brak',
                $item['room'],
                $item['semestr'] ?? 0
            );
        }
    }

    private function addDepartment(string $name, string $shortName)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Wydzial WHERE nazwa = :name AND sk = :shortName");
        $stmt->execute([':name' => $name, ':shortName' => $shortName]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Wydzial (nazwa, sk) VALUES (:name, :shortName)");
            $stmt->execute([':name' => $name, ':shortName' => $shortName]);
        }
    }

    private function addRoom(string $room, string $department)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Wydzial WHERE nazwa = :department");
        $stmt->execute([':department' => $department]);
        $result = $stmt->fetch();

        if ($result) {
            $departmentId = $result['id'];
            $stmt = $this->pdo->prepare("SELECT id FROM Sala_z_budynkiem WHERE budynek_sala = :room AND wydzial_id = :departmentId");
            $stmt->execute([':room' => $room, ':departmentId' => $departmentId]);
            if (!$stmt->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO Sala_z_budynkiem (budynek_sala, wydzial_id) VALUES (:room, :departmentId)");
                $stmt->execute([':room' => $room, ':departmentId' => $departmentId]);
            }
        } else {
            throw new \Exception("Department not found");
        }
    }

    private function addStudyTrack(string $typeShort, string $modeShort, string $mode, string $type)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Tok_studiow WHERE typ = :type AND tryb = :mode");
        $stmt->execute([':type' => $type, ':mode' => $mode]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Tok_studiow (typ, tryb, typ_sk, tryb_sk) VALUES (:type, :mode, :typeShort, :modeShort)");
            $stmt->execute([':type' => $type, ':mode' => $mode, ':typeShort' => $typeShort, ':modeShort' => $modeShort]);
        }
    }

    private function addSubject(string $name, string $modeShort, string $typeShort, string $form, int $semester, int $year)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Tok_studiow WHERE tryb_sk = :modeShort AND typ_sk = :typeShort");
        $stmt->execute([':modeShort' => $modeShort, ':typeShort' => $typeShort]);
        $result = $stmt->fetch();

        if ($result) {
            $studyTrackId = $result['id'];
            $stmt = $this->pdo->prepare("SELECT id FROM Przedmiot WHERE nazwa = :name AND forma = :form AND tok_studiow_id = :studyTrackId AND semestr = :semester AND rok = :year");
            $stmt->execute([':name' => $name, ':form' => $form, ':studyTrackId' => $studyTrackId, ':semester' => $semester, ':year' => $year]);
            if (!$stmt->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO Przedmiot (nazwa, forma, tok_studiow_id, semestr, rok) VALUES (:name, :form, :studyTrackId, :semester, :year)");
                $stmt->execute([':name' => $name, ':form' => $form, ':studyTrackId' => $studyTrackId, ':semester' => $semester, ':year' => $year]);
            }
        } else {
            throw new \Exception("Study track not found");
        }
    }

    private function addGroup(string $name)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Grupa WHERE nazwa = :name");
        $stmt->execute([':name' => $name]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Grupa (nazwa) VALUES (:name)");
            $stmt->execute([':name' => $name]);
        }
    }

    private function addLecturer(string $name, string $title)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Wykladowca WHERE nazwisko_imie = :name");
        $stmt->execute([':name' => $name]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Wykladowca (nazwisko_imie, tytul) VALUES (:name, :title)");
            $stmt->execute([':name' => $name, ':title' => $title]);
        }
    }

    private function addClass(int $id, string $startDate, string $endDate, string $substitute, string $lecturer, string $department, string $group, string $studyTrack, string $subject, string $form, string $room, int $semester)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Wykladowca WHERE nazwisko_imie = :lecturer");
        $stmt->execute([':lecturer' => $lecturer]);
        $lecturerId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT id FROM Wydzial WHERE nazwa = :department");
        $stmt->execute([':department' => $department]);
        $departmentId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT id FROM Grupa WHERE nazwa = :group");
        $stmt->execute([':group' => $group]);
        $groupId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT id FROM Tok_studiow WHERE typ_sk = :studyTrack");
        $stmt->execute([':studyTrack' => $studyTrack]);
        $studyTrackId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT id FROM Przedmiot WHERE nazwa = :subject AND forma = :form");
        $stmt->execute([':subject' => $subject, ':form' => $form]);
        $subjectId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT id FROM Sala_z_budynkiem WHERE budynek_sala = :room");
        $stmt->execute([':room' => $room]);
        $roomId = $stmt->fetchColumn();

        if ($lecturerId && $departmentId && $groupId && $studyTrackId && $subjectId && $roomId) {
            $stmt = $this->pdo->prepare("SELECT id FROM Zajecia WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO Zajecia (id, data_start, data_koniec, zastepca, semestr, wykladowca_id, wydzial_id, grupa_id, tok_studiow_id, przedmiot_id, sala_id) VALUES (:id, :startDate, :endDate, :substitute, :semester, :lecturerId, :departmentId, :groupId, :studyTrackId, :subjectId, :roomId)");
                $stmt->execute([
                    ':id' => $id,
                    ':startDate' => $startDate,
                    ':endDate' => $endDate,
                    ':substitute' => $substitute,
                    ':semester' => $semester,
                    ':lecturerId' => $lecturerId,
                    ':departmentId' => $departmentId,
                    ':groupId' => $groupId,
                    ':studyTrackId' => $studyTrackId,
                    ':subjectId' => $subjectId,
                    ':roomId' => $roomId
                ]);
            }
        }
    }

    private function addStudentGroup(int $studentId, string $group)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM Student WHERE id = :studentId");
        $stmt->execute([':studentId' => $studentId]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO Student (id) VALUES (:studentId)");
            $stmt->execute([':studentId' => $studentId]);
        }
    }
}
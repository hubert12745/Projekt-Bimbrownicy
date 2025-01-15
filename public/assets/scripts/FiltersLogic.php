<?php
require_once __DIR__ . '/../../../src/Service/Config.php';
use App\Service\Config;

try {
    $pdo = new PDO(
        Config::get('db_dsn'),
        Config::get('db_user'),
        Config::get('db_pass')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $filters = [
        'wydzial' => $_GET['wydzial'] ?? '',
        'wykladowca' => $_GET['wykladowca'] ?? '',
        'sala' => $_GET['sala'] ?? '',
        'przedmiot' => $_GET['przedmiot'] ?? '',
        'grupa' => $_GET['grupa'] ?? '',
        'forma' => $_GET['forma'] ?? '',
        'typStudiow' => $_GET['typStudiow'] ?? '',
        'semestrStudiow' => $_GET['semestrStudiow'] ?? '',
        'rokStudiow' => $_GET['rokStudiow'] ?? ''
    ];

    $query = "SELECT * FROM Lesson WHERE 1=1";

    if ($filters['wydzial']) {
        $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE faculty_id IN (SELECT faculty_id FROM Faculty WHERE faculty_name LIKE :wydzial))";
    }
    if ($filters['wykladowca']) {
        $query .= " AND worker_id IN (SELECT worker_id FROM Worker WHERE full_name LIKE :wykladowca)";
    }
    if ($filters['sala']) {
        $query .= " AND room_id IN (SELECT room_id FROM Room WHERE room_name LIKE :sala)";
    }
    if ($filters['przedmiot']) {
        $query .= " AND subject_id IN (SELECT subject_id FROM Subject WHERE subject_name LIKE :przedmiot)";
    }
    if ($filters['grupa']) {
        $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE group_name LIKE :grupa)";
    }
    if ($filters['forma']) {
        $query .= " AND lesson_form LIKE :forma";
    }
    if ($filters['typStudiow']) {
        $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE type_of_study LIKE :typStudiow)";
    }
    if ($filters['semestrStudiow']) {
        $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE semester LIKE :semestrStudiow)";
    }
    if ($filters['rokStudiow']) {
        $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE year LIKE :rokStudiow)";
    }

    $stmt = $pdo->prepare($query);

    foreach ($filters as $key => $value) {
        if ($value) {
            $stmt->bindValue(":$key", "%$value%");
        }
    }

    // Log the query and parameters
    error_log('SQL Query: ' . $query);
    error_log('Parameters: ' . json_encode($filters));

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($results);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
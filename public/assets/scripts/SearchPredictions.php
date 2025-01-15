<?php

require_once __DIR__ . '/../../../src/Service/Config.php';
use App\Service\Config;

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$filter = $_GET['filter'] ?? '';

if ($query && $filter) {
    try {
        $pdo = new PDO(
            Config::get('db_dsn'),
            Config::get('db_user'),
            Config::get('db_pass')
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $column = '';
        $table = '';
        switch ($filter) {
            case 'wydzial':
                $column = 'faculty_name';
                $table = 'Faculty';
                break;
            case 'wykladowca':
                $column = 'full_name';
                $table = 'Worker';
                break;
            case 'sala':
                $column = 'room_name';
                $table = 'Room';
                break;
            case 'przedmiot':
                $column = 'subject_name';
                $table = 'Subject';
                break;
            case 'grupa':
                $column = 'group_name';
                $table = 'ClassGroup';
                break;
            case 'forma':
                $column = 'lesson_form';
                $table = 'Lesson';
                break;
            case 'typStudiow':
                $column = 'field_of_study';
                $table = 'ClassGroup';
                break;
            case 'semestrStudiow':
                $column = 'semester';
                $table = 'ClassGroup';
                break;
            case 'rokStudiow':
                $column = 'department';
                $table = 'ClassGroup';
                break;
            default:
                echo json_encode([]);
                exit;
        }

        // Debugging information
        error_log("Query: $query");
        error_log("Filter: $filter");
        error_log("SQL: SELECT DISTINCT $column FROM $table WHERE $column LIKE :query LIMIT 10");

        $stmt = $pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column LIKE :query LIMIT 10");
        $stmt->execute(['query' => '%' . $query . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Debugging information
        error_log("Results: " . json_encode($results));

        echo json_encode($results);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
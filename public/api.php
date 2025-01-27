<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

use App\Service\Config;
/*
 * This script is an API endpoint for fetching schedule data.
 * It accepts GET requests with optional query parameters.
 * The parameters are used to filter the results.
 * The script returns a JSON response with the results.
 * Example usage:
 * GET /api.php?faculty=WI&lecturer=Kowalski&room=101&subject=Matematyka&group=1A&form=Wykład&studyType=Stacjonarne&semester=1&year=2021&studentId=123456&startDate=2021-10-01&endDate=2021-10-31
 * Sample link: http://localhost/api.php?faculty=WI&lecturer=Kowalski&room=101&subject=Matematyka&group=1A&form=Wykład&studyType=Stacjonarne&semester=1&year=2021&studentId=123456&startDate=2021-10-01&endDate=2021-10-31
 */

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle the request
switch ($method) {
    case 'GET':
        $filters = [
            'faculty' => $_GET['faculty'] ?? '',
            'lecturer' => $_GET['lecturer'] ?? '',
            'room' => $_GET['room'] ?? '',
            'subject' => $_GET['subject'] ?? '',
            'group' => $_GET['group'] ?? '',
            'form' => $_GET['form'] ?? '',
            'studyType' => $_GET['studyType'] ?? '',
            'semester' => $_GET['semester'] ?? '',
            'year' => $_GET['year'] ?? '',
            'studentId' => $_GET['studentId'] ?? '',
            'startDate' => $_GET['startDate'] ?? '',
            'endDate' => $_GET['endDate'] ?? ''
        ];

        $query = "SELECT * FROM Lesson WHERE 1=1";

        if ($filters['faculty']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE faculty_id IN (SELECT faculty_id FROM Faculty WHERE faculty_name LIKE :faculty))";
        }
        if ($filters['lecturer']) {
            $query .= " AND worker_id IN (SELECT worker_id FROM Worker WHERE full_name LIKE :lecturer)";
        }
        if ($filters['room']) {
            $query .= " AND room_id IN (SELECT room_id FROM Room WHERE room_name LIKE :room)";
        }
        if ($filters['subject']) {
            $query .= " AND subject_id IN (SELECT subject_id FROM Subject WHERE subject_name LIKE :subject)";
        }
        if ($filters['group']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE group_name LIKE :group)";
        }
        if ($filters['form']) {
            $query .= " AND lesson_form LIKE :form";
        }
        if ($filters['studyType']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE type_of_study LIKE :studyType)";
        }
        if ($filters['semester']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE semester LIKE :semester)";
        }
        if ($filters['year']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE year LIKE :year)";
        }
        if ($filters['studentId']) {
            $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE group_id IN (SELECT group_id FROM StudentGroup WHERE student_id LIKE :studentId))";
        }
        if ($filters['startDate']) {
            $query .= " AND lesson_start >= :startDate";
        }
        if ($filters['endDate']) {
            $query .= " AND lesson_end <= :endDate";
        }

        $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare($query);

        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'startDate' || $key === 'endDate') {
                    $stmt->bindValue(":$key", $value);
                } else {
                    $stmt->bindValue(":$key", "%$value%");
                }
            }
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        break;
}
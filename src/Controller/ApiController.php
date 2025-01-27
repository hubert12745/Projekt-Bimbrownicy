<?php

namespace App\Controller;

use App\Service\Config;
use App\Model\Lesson;
use App\Service\Router;
use App\Service\Templating;

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

class ApiController
{
    public function scheduleAction(Templating $templating, Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
            $lesson = new Lesson();
            $lessons = $lesson->findByFilters($filters);
            error_log(print_r(json_encode($lessons), true));
            header('Content-Type: application/json');
            return json_encode($lessons);
        }
        return null;
    }

}


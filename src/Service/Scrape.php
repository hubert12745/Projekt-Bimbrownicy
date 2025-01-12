<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '../autoload.php';
require_once __DIR__ . '/../Model/Lesson.php';
require_once __DIR__ . '/../Model/Subject.php';
require_once __DIR__ . '/../Model/Worker.php';
require_once __DIR__ . '/../Model/ClassGroup.php';
require_once __DIR__ . '/../Model/Student.php';
require_once __DIR__ . '/../Model/Room.php';
require_once __DIR__ . '/../Model/Faculty.php';
require_once __DIR__ . '/../Model/StudentGroup.php';
require_once __DIR__ . '/ScrapeData.php';

use App\Service\ScrapeData;
use App\Service\Config;

function scrape(string $faculty, string $start, string $end): void
{
    $scrape = new ScrapeData();
    $scrape->fatchData($faculty, $start, $end);
}

try {
    $faculties =  ['WI', 'WE'];
    $start = '2025-01-07';
    $end = '2025-01-08';
    foreach ($faculties as $faculty) {
        scrape($faculty, $start, $end);
    }

    echo 'Scraping successful' . PHP_EOL;
} catch (Exception $e) {
    echo 'Scraping failed: ' . $e->getMessage() . PHP_EOL;
}

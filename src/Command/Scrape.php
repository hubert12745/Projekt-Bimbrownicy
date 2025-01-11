<?php
//require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '../autoload.php';

use App\Service\Config;
use App\Service\ScrapeData;
use PDO;
use PDOException;

function createPDOConnection(): PDO
{
    $pdo = new PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function scrapeData(PDO $pdo, string $department, string $start, string $end): void
{
    $scrapeData = new ScrapeData($pdo);
    $scrapeData->fetchData($department, $start, $end);
}

try {
    $pdo = createPDOConnection();
    $department = 'WI';
    $start = '2024-10-07';
    $end = '2024-10-08';
    scrapeData($pdo, $department, $start, $end);
    echo "Data scraped and inserted successfully.";
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
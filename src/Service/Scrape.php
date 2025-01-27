<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '../autoload.php';


use App\Service\ScrapeData;

function scrape(string $faculty, string $start, string $end): void
{
    $scrape = new ScrapeData();
    $scrape->fetchData($faculty, $start, $end);
}

try {
    $startTime = microtime(true);
//    $faculties =  ['WA','WBiHZ','WBiIS','WE','WEkon','WI','WIMiM','WKSiR','WNoZiR','WTMiT','WTiICH'];
    $faculties =  ['WI'];
    $start = '2025-01-20';
    $end = '2025-01-31';
    foreach ($faculties as $faculty) {
        scrape($faculty, $start, $end);
    }
    $timeElapsed = microtime(true) - $startTime;
    echo "Scraping successful, time elapsed: {$timeElapsed}" . PHP_EOL;
} catch (Exception $e) {
    echo 'Scraping failed: ' . $e->getMessage() . PHP_EOL;
}

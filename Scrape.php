<?php
require 'src/Service/ScrapeData.php';
require 'config/config.php';

if (!isset($config)) {
    die('Configuration not found.');
}

$dsn = $config['db_dsn'];
$username = $config['db_user'];
$password = $config['db_pass'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $scrapeData = new \App\Service\ScrapeData($pdo);
    $department = 'WI';
    $scrapeData->fetchData($department);

    echo "Data scraped and inserted successfully.";
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
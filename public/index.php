<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

$config = new \App\Service\Config();

$templating = new \App\Service\Templating();
$router = new \App\Service\Router();

$action = $_REQUEST['action'] ?? null;
$view = null;

switch ($action) {
    case 'schedule-index':
    case null:
        $controller = new \App\Controller\ScheduleController();
        $view = $controller->indexAction($templating, $router);
        break;
    case 'info':
        $controller = new \App\Controller\InfoController();
        $view = $controller->infoAction();
        break;
    case 'schedule-filter':
        $controller = new \App\Controller\ScheduleController();
        $view = $controller->filterAction($templating, $router);
        break;
    case 'search-predictions':
        $controller = new \App\Controller\ScheduleController();
        $view = $controller->searchAction($templating, $router);
        break;
    default:
        $view = 'Not found';
        break;
}

if ($view) {
    echo $view;
}
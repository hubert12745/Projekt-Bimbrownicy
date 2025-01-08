<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

$config = new \App\Service\Config();

$templating = new \App\Service\Templating();
$router = new \App\Service\Router();

$action = $_REQUEST['action'] ?? null;
$view = null;

switch ($action) {
    case 'schedule-index':
        $controller = new \App\Controller\ScheduleController();
        $view = $controller->indexAction($templating, $router);
        break;
    case 'schedule-show':
        if (isset($_REQUEST['id'])) {
            $controller = new \App\Controller\ScheduleController();
            $view = $controller->showAction((int)$_REQUEST['id'], $templating, $router);
        } else {
            throw new NotFoundException("Missing schedule ID");
        }
        break;
    case 'schedule-create':
        $controller = new \App\Controller\ScheduleController();
        $view = $controller->createAction($_REQUEST['schedule'] ?? null, $templating, $router);
        break;
    case 'schedule-edit':
        if (isset($_REQUEST['id'])) {
            $controller = new \App\Controller\ScheduleController();
            $view = $controller->editAction((int)$_REQUEST['id'], $_REQUEST['schedule'] ?? null, $templating, $router);
        }
        break;
    case 'schedule-delete':
        if (isset($_REQUEST['id'])) {
            $controller = new \App\Controller\ScheduleController();
            $view = $controller->deleteAction((int)$_REQUEST['id'], $router);
        }
        break;
    case 'info':
        $controller = new \App\Controller\InfoController();
        $view = $controller->infoAction();
        break;
    default:
        $view = 'Not found';
        break;
}

if ($view) {
    echo $view;
}
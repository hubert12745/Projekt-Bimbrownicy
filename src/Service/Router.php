<?php
namespace App\Service;

class Router
{
    private $routes = [];

    public function generatePath(string $action, ?array $params = []): string
    {
        $query = $action ? http_build_query(array_merge(['action' => $action], $params)) : null;
        $path = "/index.php" . ($query ? "?$query" : null);
        return $path;
    }

    public function redirect($path): void
    {
        header("Location: $path");
    }

    public function addRoute(string $name, string $path, array $controllerAction): void
    {
        $this->routes[$name] = [
            'path' => $path,
            'controller' => $controllerAction[0],
            'action' => $controllerAction[1]
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}

// Add routes for ScheduleController
$router = new Router();
$router->addRoute('schedule-index', '/schedule', [\App\Controller\ScheduleController::class, 'indexAction']);
$router->addRoute('schedule-show', '/schedule/{id}', [\App\Controller\ScheduleController::class, 'showAction']);
$router->addRoute('schedule-create', '/schedule/create', [\App\Controller\ScheduleController::class, 'createAction']);
$router->addRoute('schedule-edit', '/schedule/{id}/edit', [\App\Controller\ScheduleController::class, 'editAction']);
$router->addRoute('schedule-delete', '/schedule/{id}/delete', [\App\Controller\ScheduleController::class, 'deleteAction']);
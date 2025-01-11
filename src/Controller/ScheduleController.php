<?php
namespace App\Controller;

use App\Model\Schedule;
use App\Service\Router;
use App\Service\Templating;
use App\Exception\NotFoundException;

class ScheduleController
{
    public function indexAction(Templating $templating, Router $router): ?string
    {
//        $schedules = Schedule::findAll();
        $html = $templating->render('schedule/index.html.php', [
          //  'schedules' => $schedules,
            'router' => $router,
        ]);
        return $html;
    }

//    public function showAction(int $scheduleId, Templating $templating, Router $router): ?string
//    {
//        $schedule = Schedule::find($scheduleId);
//        if (! $schedule) {
//            throw new NotFoundException("Missing schedule with id $scheduleId");
//        }
//
//        $html = $templating->render('schedule/show.html.php', [
//            'schedule' => $schedule,
//            'router' => $router,
//        ]);
//        return $html;
//    }
//
//    public function createAction(?array $requestPost, Templating $templating, Router $router): ?string
//    {
//        if ($requestPost) {
//            $schedule = Schedule::fromArray($requestPost);
//            // @todo missing validation
//            $schedule->save();
//
//            $path = $router->generatePath('schedule-index');
//            $router->redirect($path);
//            return null;
//        } else {
//            $schedule = new Schedule();
//        }
//
//        $html = $templating->render('schedule/create.html.php', [
//            'schedule' => $schedule,
//            'router' => $router,
//        ]);
//        return $html;
//    }
//
//    public function editAction(int $scheduleId, ?array $requestPost, Templating $templating, Router $router): ?string
//    {
//        $schedule = Schedule::find($scheduleId);
//        if (! $schedule) {
//            throw new NotFoundException("Missing schedule with id $scheduleId");
//        }
//
//        if ($requestPost) {
//            $schedule->fill($requestPost);
//            // @todo missing validation
//            $schedule->save();
//
//            $path = $router->generatePath('schedule-index');
//            $router->redirect($path);
//            return null;
//        }
//
//        $html = $templating->render('schedule/edit.html.php', [
//            'schedule' => $schedule,
//            'router' => $router,
//        ]);
//        return $html;
//    }
//
//    public function deleteAction(int $scheduleId, Router $router): ?string
//    {
//        $schedule = Schedule::find($scheduleId);
//        if (! $schedule) {
//            throw new NotFoundException("Missing schedule with id $scheduleId");
//        }
//
//        $schedule->delete();
//        $path = $router->generatePath('schedule-index');
//        $router->redirect($path);
//        return null;
//    }
}
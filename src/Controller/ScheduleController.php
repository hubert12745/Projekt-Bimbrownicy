<?php
namespace App\Controller;

use App\Service\Router;
use App\Model\FiltersLogic;
use App\Service\Templating;
use App\Exception\NotFoundException;
use App\Model\SearchPredictions;


class ScheduleController
{
    public function indexAction(Templating $templating, Router $router): ?string
    {
        $html = $templating->render('schedule/index.html.php', [
            'router' => $router,
        ]);
        return $html;
    }

    public function filterAction(Templating $templating, Router $router): ?string
    {
        $filters = [
            'wydzial' => $_GET['wydzial'] ?? '',
            'wykladowca' => $_GET['wykladowca'] ?? '',
            'sala' => $_GET['sala'] ?? '',
            'przedmiot' => $_GET['przedmiot'] ?? '',
            'grupa' => $_GET['grupa'] ?? '',
            'forma' => $_GET['forma'] ?? '',
            'typStudiow' => $_GET['typStudiow'] ?? '',
            'semestrStudiow' => $_GET['semestrStudiow'] ?? '',
            'rokStudiow' => $_GET['rokStudiow'] ?? '',
            'nrAlbumu' => $_GET['nrAlbumu'] ?? ''
        ];

        $results = FiltersLogic::applyFilters($filters);

        header('Content-Type: application/json');
        echo json_encode($results);
        return null;
    }

    public function searchAction(Templating $templating, Router $router)
    {
        $query = $_GET['query'] ?? '';
        $filter = $_GET['filter'] ?? '';

        $results = SearchPredictions::getPredictions($query, $filter);

        header('Content-Type: application/json');
        echo json_encode($results);
        return null;
    }
}
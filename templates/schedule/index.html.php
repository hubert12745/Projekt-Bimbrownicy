<?php

/** @var \App\Model\Schedule[] $schedules */
/** @var \App\Service\Router $router */

$title = 'Schedule List';
$bodyClass = 'index';

ob_start(); ?>
    <h1>Schedule List</h1>

    <a href="<?= $router->generatePath('schedule-create') ?>">Create new</a>

    <ul class="index-list">
        <?php foreach ($schedules as $schedule): ?>
            <li><h3>Schedule ID: <?= $schedule->getId() ?></h3>
                <ul class="action-list">
                    <li><a href="<?= $router->generatePath('schedule-show', ['id' => $schedule->getId()]) ?>">Details</a></li>
                    <li><a href="<?= $router->generatePath('schedule-edit', ['id' => $schedule->getId()]) ?>">Edit</a></li>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>

<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
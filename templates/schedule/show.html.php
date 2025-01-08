<?php

/** @var \App\Model\Schedule $schedule */
/** @var \App\Service\Router $router */

$title = "Schedule Details ({$schedule->getId()})";
$bodyClass = 'show';

ob_start(); ?>
    <h1>Schedule Details</h1>
    <article>
        <p><strong>ID:</strong> <?= htmlspecialchars($schedule->getId()) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($schedule->getDataStart()) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($schedule->getDataKoniec()) ?></p>
        <p><strong>Lecturer:</strong> <?= htmlspecialchars($schedule->getLecturer()) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($schedule->getDepartment()) ?></p>
        <p><strong>Group:</strong> <?= htmlspecialchars($schedule->getGroup()) ?></p>
        <p><strong>Study Track:</strong> <?= htmlspecialchars($schedule->getStudyTrack()) ?></p>
        <p><strong>Subject:</strong> <?= htmlspecialchars($schedule->getSubject()) ?></p>
        <p><strong>Room:</strong> <?= htmlspecialchars($schedule->getRoom()) ?></p>
        <p><strong>Semester:</strong> <?= htmlspecialchars($schedule->getSemester()) ?></p>
    </article>

    <ul class="action-list">
        <li><a href="<?= $router->generatePath('schedule-index') ?>">Back to list</a></li>
        <li><a href="<?= $router->generatePath('schedule-edit', ['id'=> $schedule->getId()]) ?>">Edit</a></li>
    </ul>
<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
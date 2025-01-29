<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!--    <meta name="viewport"-->
    <!--          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">-->
    <!--    <meta http-equiv="X-UA-Compatible" content="ie=edge">-->
    <link rel="stylesheet" href="/assets/dist/style.min.css">
    <title><?= $title ?? 'Custom Framework' ?></title>
</head>
<body <?= isset($bodyClass) ? "class='$bodyClass'" : '' ?>>
<main><?= $main ?? null ?></main>
<div class="accessibility-controls">
    <button id="toggle-contrast" class="accessibility-button">Wysoki kontrast</button>
    <button id="toggle-font-size" class="accessibility-button">Większa czcionka</button>
</div>
</body>
</html>
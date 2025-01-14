<?php

/** @var \App\Model\Schedule[] $schedules */
/** @var \App\Service\Router $router */

$title = 'Schedule List';
$bodyClass = 'index';

ob_start(); ?>
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <!-- Przycisk, który pokaże panel filtrów -->
            <button class="button" id="showFiltersBtn">Pokaż filtry</button>

            <!-- Przyciski normalnie widoczne -->

            <button class="button" id="showFavouritesBtn">ULUBIONE </button>
            <button class="button">STATYSTYKI</button>

            <!-- PANEL FILTRÓW nakrywający -->

            <div class="filters" id="filters">
                <!-- Przycisk do chowania filtrów -->
                <button class="close-button" id="closeFiltersBtn">Ukryj filtry</button>

                <!-- Zawartość panelu filtrów -->
                <div class="form-group">
                    <label for="wydzial">Wydział</label>
                    <input type="text" id="wydzial" placeholder="np. Wydział Informatyki" />
                </div>
                <div class="form-group">
                    <label for="wykladowca">Wykładowca</label>
                    <input type="text" id="wykladowca" placeholder="np. Jan Kowalski" />
                </div>
                <div class="form-group">
                    <label for="sala">Sala</label>
                    <input type="text" id="sala" placeholder="np. A101" />
                </div>
                <div class="form-group">
                    <label for="przedmiot">Przedmiot</label>
                    <input type="text" id="przedmiot" placeholder="np. Algorytmy" />
                </div>
                <div class="form-group">
                    <label for="grupa">Grupa</label>
                    <input type="text" id="grupa" placeholder="np. II Inf 1A" />
                </div>
                <div class="form-group">
                    <label for="nrAlbumu">Nr. Albumu</label>
                    <input type="text" id="nrAlbumu" placeholder="np. 123456" />
                </div>
                <div class="form-group">
                    <label for="forma">Forma przedmiotu</label>
                    <input type="text" id="forma" placeholder="np. Wykład, Ćwiczenia" />
                </div>
                <div class="form-group">
                    <label for="typStudiow">Typ studiów</label>
                    <input type="text" id="typStudiow" placeholder="np. Stacjonarne" />
                </div>
                <div class="form-group">
                    <label for="semestrStudiow">Semestr studiów</label>
                    <input type="text" id="semestrStudiow" placeholder="np. 3" />
                </div>
                <div class="form-group">
                    <label for="rokStudiow">Rok studiów</label>
                    <input type="text" id="rokStudiow" placeholder="np. II" />
                </div>

                <div class="filter-buttons">
                    <button type="reset">Resetuj filtry</button>
                    <button type="button">Zastosuj filtry</button>

                </div>
            </div>
            <div class="filters" id="favourites">

                <button class="close-button" id="closeFavouritesBtn">Ukryj ulubione</button>
                <ul id="buttonList">

                </ul>
            </div>

        </div>

        <!-- Główna część (header + tabela) -->
        <div class="main">
            <!-- Pasek nawigacji -->
            <div class="header">
                <div class="nav">
                    <button class="nav-button" id="prevWeekBtn">&lt; Poprzedni</button>
                    <button class="nav-button" id="nextWeekBtn">Następny &gt;</button>
                    <button class="nav-button" id="addEventBtn">Dodaj zajęcia</button>
                    <button class="nav-button" id="addFavourtiesBtn">&lt;3</button>
                    <div class="view-buttons">
                        <button class="view-button">Semestr</button>
                        <button class="view-button">Miesiąc</button>
                        <button class="view-button">Tydzień</button>
                        <button class="view-button">Dzień</button>
                    </div>
                </div>
            </div>

            <!-- Kontener na tabelę -->
            <div class="schedule-container">
                <table class="schedule-table">
                    <thead>
                    <tr>
                        <th>Godz.</th>
                        <!-- 7 kolumn na dni tygodnia (pon..nd) -->
                        <th id="day-0">pon (DD.MM)</th>
                        <th id="day-1">wt (DD.MM)</th>
                        <th id="day-2">śr (DD.MM)</th>
                        <th id="day-3">czw (DD.MM)</th>
                        <th id="day-4">pt (DD.MM)</th>
                        <th id="day-5">sob (DD.MM)</th>
                        <th id="day-6">nd (DD.MM)</th>
                    </tr>
                    </thead>
                    <tbody id="schedule-body">
                    <!-- Wygenerujemy wiersze (7..19) w skrypcie -->
                    </tbody>
                </table>
            </div>

            <!-- Przykładowa legenda (opcjonalnie) -->
            <div class="legend">
                <strong>Legenda:</strong>
                <span class="dot lab"></span> laboratorium
                <span class="dot wyklad"></span> wykład
                <span class="dot lektorat"></span> lektorat
                <span class="dot projekt"></span> projekt
                <span class="dot audytoryjne"></span> audytoryjne
                <span class="dot egzamin"></span> egzamin
            </div>
        </div> <!-- .main -->

    </div> <!-- .container -->

    <!-- Skrypt główny -->
    <script src="/assets/scripts/script.js" defer></script>
<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
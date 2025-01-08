<?php

class LogikaFiltrow {

    /**
     * Zastosowanie filtrów do danych wejściowych
     *
     * @param array $dane - Lista danych do filtrowania
     * @param array $kryteria - Kryteria filtrów
     * @return array - Przefiltrowane dane
     */
    public function zastosujFiltry(array $dane, array $kryteria): array {
        $wynik = $dane;

        // Filtrowanie po wydziale
        if (isset($kryteria['wydzial'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['wydzial'] === $kryteria['wydzial'];
            });
        }

        // Filtrowanie po wykładowcy
        if (isset($kryteria['wykladowca'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['wykladowca'] === $kryteria['wykladowca'];
            });
        }

        // Filtrowanie po sali
        if (isset($kryteria['sala'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['sala'] === $kryteria['sala'];
            });
        }

        // Filtrowanie po przedmiocie
        if (isset($kryteria['przedmiot'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['przedmiot'] === $kryteria['przedmiot'];
            });
        }

        // Filtrowanie po grupie
        if (isset($kryteria['grupa'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['grupa'] === $kryteria['grupa'];
            });
        }

        // Filtrowanie po numerze albumu studenta
        if (isset($kryteria['numer_albumu'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['numer_albumu'] === $kryteria['numer_albumu'];
            });
        }

        // Filtrowanie po formie przedmiotu
        if (isset($kryteria['forma_przedmiotu'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['forma_przedmiotu'] === $kryteria['forma_przedmiotu'];
            });
        }

        // Filtrowanie po typie studiów
        if (isset($kryteria['typ_studiow'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['typ_studiow'] === $kryteria['typ_studiow'];
            });
        }

        // Filtrowanie po semestrze studiów
        if (isset($kryteria['semestr'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['semestr'] == $kryteria['semestr']; // Używamy == dla porównania liczbowego
            });
        }

        // Filtrowanie po roku studiów
        if (isset($kryteria['rok_studiow'])) {
            $wynik = array_filter($wynik, function($item) use ($kryteria) {
                return $item['rok_studiow'] == $kryteria['rok_studiow']; // Używamy == dla porównania liczbowego
            });
        }

        return $wynik;
    }
}

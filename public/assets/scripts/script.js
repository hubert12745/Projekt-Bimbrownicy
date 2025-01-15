// Przycisk do wyświetlania aktualnego tygodnia
document.getElementById('showCurrentWeekBtn').addEventListener('click', () => {
    currentMonday = getMondayOfCurrentWeek(new Date());  // Ustawienie na obecny tydzień
    renderWeek();  // Odświeżenie kalendarza
    highlightToday();  // Podświetlenie dzisiejszego dnia
});

document.getElementById('changeWeekBtn').addEventListener('click', () => {
    const weekPicker = document.getElementById('weekPicker').value;
    if (!weekPicker) {
        alert("Proszę wybrać datę początkową tygodnia.");
        return;
    }

    // Konwersja na obiekt daty i dostosowanie do poniedziałku
    const selectedDate = new Date(weekPicker);
    currentMonday = getMondayOfCurrentWeek(selectedDate);  // Ustawienie nowego tygodnia
    renderWeek();  // Odświeżenie kalendarza
});

/************************
 DANE I ZMIENNE GŁÓWNE
 ************************/

// Obliczamy poniedziałek bieżącego tygodnia (dla uproszczenia)
let currentMonday = getMondayOfCurrentWeek(new Date());

// Tablica przykładowych wydarzeń
// Każde ma: date (YYYY-MM-DD), start, end (HH:MM), type (lab/wyklad...), title
const events = [
    {
        date: "2025-01-20",   // poniedziałek
        start: "07:15",       // 7:15
        end: "08:50",         // 8:50
        type: "wyklad",
        title: "Wykład poranny"
    },
    {
        date: "2025-01-20",
        start: "10:15",
        end: "12:00",
        type: "lab",
        title: "Sieci komputerowe (L)"
    },
    {
        date: "2025-01-21",   // wtorek
        start: "08:15",
        end: "11:00",
        type: "projekt",
        title: "Inżynieria projekt zespołowy"
    },
    {
        date: "2025-01-21",
        start: "14:15",
        end: "18:00",
        type: "lektorat",
        title: "Język angielski 2 (Lek.)"
    },
];

/****************************
 FUNKCJA TWORZENIA TABELI
 ****************************/

function buildScheduleBody() {
    const tbody = document.getElementById("schedule-body");
    tbody.innerHTML = "";

    // Wiersze dla godzin [7..19]
    for (let hour = 7; hour <= 19; hour++) {
        const tr = document.createElement("tr");

        // Pierwsza kolumna: wyświetlamy numer godziny
        const hourTd = document.createElement("td");
        hourTd.textContent = String(hour).padStart(2, '0');
        tr.appendChild(hourTd);

        // 7 kolumn na dni (0..6)
        for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
            const td = document.createElement("td");
            td.id = `day${dayIndex}-hour${hour}`;
            tr.appendChild(td);
        }

        tbody.appendChild(tr);
    }
}

/****************************
 FUNKCJA PODŚWIETLAJĄCA DZISIEJSZY DZIEŃ
 ****************************/

function highlightToday() {
    // 1) Usuwamy poprzednie podświetlenia z nagłówków dni i komórek
    for (let i = 0; i < 7; i++) {
        const dayHeader = document.getElementById(`day-${i}`);
        dayHeader.classList.remove('todayHighlight');
        for (let hour = 7; hour <= 19; hour++) {
            const cell = document.getElementById(`day${i}-hour${hour}`);
            if (cell) {
                cell.classList.remove('todayHighlight');
            }
        }
    }

    // 2) Obliczamy, czy dziś (z wyzerowanymi godzinami) mieści się w aktualnym tygodniu
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Wyzerowanie godzin, minut, sekund

    const diffDays = (today - currentMonday) / (1000 * 60 * 60 * 24);
    // diffDays >= 0 oznacza, że "dzisiejszy" dzień jest taki sam lub późniejszy niż currentMonday
    // diffDays < 7 oznacza, że jest w ciągu kolejnych 7 dni (0..6)
    if (diffDays >= 0 && diffDays < 7) {
        const dayIndex = Math.floor(diffDays);

        // 3) Podświetlamy nagłówek i komórki (godziny) dla dzisiejszego dnia
        const dayHeader = document.getElementById(`day-${dayIndex}`);
        dayHeader.classList.add('todayHighlight');

        for (let hour = 7; hour <= 19; hour++) {
            const cell = document.getElementById(`day${dayIndex}-hour${hour}`);
            if (cell) {
                cell.classList.add('todayHighlight');
            }
        }
    }
}

/****************************
 RENDEROWANIE TYGODNIA
 ****************************/

function renderWeek(filteredEvents = events) {
    // 1) Budujemy pustą tabelę (wiersze + kolumny)
    buildScheduleBody();

    // 2) Ustawiamy nagłówki dni: "pon (20.01)", "wt (21.01)", ...
    for (let i = 0; i < 7; i++) {
        const dayHeader = document.getElementById(`day-${i}`);
        const thisDay = new Date(currentMonday);
        thisDay.setDate(currentMonday.getDate() + i);
        dayHeader.textContent = formatDayHeader(thisDay, i);
    }

    // 3) Wypełniamy tabelę wydarzeniami
    filteredEvents.forEach(ev => {
        const evDate = new Date(ev.date);
        const diffDays = (evDate - currentMonday) / (1000*60*60*24);
        if (diffDays >= 0 && diffDays < 7) {
            const dayIndex = Math.floor(diffDays);
            drawEventInCells(dayIndex, ev);
        }
    });
}

/****************************
 RYSOWANIE WYDARZENIA
 ****************************/
/**
 * Umieszcza event (start..end) w kolejnych komórkach (rowach) tak,
 * by w każdej godzinie pojawiał się tylko "fragment" eventu.
 */
function drawEventInCells(dayIndex, event) {
    // Rozbij start/end na minuty od północy
    const [startHour, startMin] = event.start.split(":").map(Number);
    const [endHour, endMin] = event.end.split(":").map(Number);

    const startTotal = startHour * 60 + startMin;
    const endTotal   = endHour   * 60 + endMin;

    for (let hour = startHour; hour <= endHour; hour++) {
        if (hour < 7 || hour > 19) continue;

        const cellId = `day${dayIndex}-hour${hour}`;
        const td = document.getElementById(cellId);
        if (!td) continue;

        // Początek i koniec w tej godzinie
        const hourStart = Math.max(startTotal, hour * 60);
        const hourEnd   = Math.min(endTotal, (hour+1)*60);
        const duration  = hourEnd - hourStart;
        if (duration <= 0) continue;

        // Wysokość jednej godziny = 60px
        const rowHeight = 60;
        const offsetTop   = ((hourStart % 60) / 60) * rowHeight;
        const blockHeight = (duration / 60) * rowHeight;

        // Tworzymy bloczek .event-block
        const div = document.createElement("div");
        div.classList.add("event-block", event.type);
        div.textContent = `${event.title} (${event.start} - ${event.end})`;

        div.style.top = offsetTop + "px";
        div.style.height = blockHeight + "px";

        td.appendChild(div);
    }
}

/****************************
 FUNKCJE POMOCNICZE
 ****************************/

/**
 * Zwraca Date będącą poniedziałkiem aktualnego tygodnia.
 */
function getMondayOfCurrentWeek(date) {
    const newDate = new Date(date);
    const day = newDate.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    newDate.setDate(newDate.getDate() + diff);
    newDate.setHours(0, 0, 0, 0);
    return newDate;
}

/**
 * Format nagłówka: "pon (20.01)"
 */
function formatDayHeader(dateObj, dayIndex) {
    const dayNames = ["pon", "wt", "śr", "czw", "pt", "sob", "nd"];
    const dd = String(dateObj.getDate()).padStart(2,'0');
    const mm = String(dateObj.getMonth()+1).padStart(2,'0');
    return `${dayNames[dayIndex]} (${dd}.${mm})`;
}

/****************************
 PRZYCISKI NAWIGACJI
 ****************************/

function shiftWeek(n) {
    currentMonday.setDate(currentMonday.getDate() + 7*n);
    renderWeek();
}

function addEvent() {
    const dayIndex = prompt("Dzień tygodnia (0=pon, ... 6=nd):");
    if (dayIndex === null) return;
    const start = prompt("Start (HH:MM), np. 07:15:");
    if (!start) return;
    const end = prompt("Koniec (HH:MM), np. 09:00:");
    if (!end) return;
    const title = prompt("Tytuł zajęć:");
    if (!title) return;
    const type = prompt("Typ (lab, wyklad, projekt, lektorat, itd.):") || "wyklad";

    const d = new Date(currentMonday);
    d.setDate(d.getDate() + parseInt(dayIndex));
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');

    events.push({
        date: `${yyyy}-${mm}-${dd}`,
        start,
        end,
        type,
        title
    });

    renderWeek();
}

/****************************
 OBSŁUGA PANELU FILTRÓW
 ****************************/

function applyFilters() {
    const filters = {
        wydzial: document.getElementById('wydzial').value,
        wykladowca: document.getElementById('wykladowca').value,
        sala: document.getElementById('sala').value,
        przedmiot: document.getElementById('przedmiot').value,
        grupa: document.getElementById('grupa').value,
        forma: document.getElementById('forma').value,
        typStudiow: document.getElementById('typStudiow').value,
        semestrStudiow: document.getElementById('semestrStudiow').value,
        rokStudiow: document.getElementById('rokStudiow').value
    };

    const queryString = new URLSearchParams(filters).toString();

    fetch(`/assets/scripts/FiltersLogic.php?${queryString}`)
        .then(response => response.json())
        .then(data => {
            console.log('Returned data:', data); // Log the returned data
            renderWeek(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // Filtry
    const showFiltersBtn = document.getElementById('showFiltersBtn');
    const closeFiltersBtn = document.getElementById('closeFiltersBtn');
    const filtersPanel = document.getElementById('filters');

    showFiltersBtn.addEventListener('click', () => {
        filtersPanel.classList.add('active');
    });

    closeFiltersBtn.addEventListener('click', () => {
        filtersPanel.classList.remove('active');
    });

    // Ulubione
    const showFavouritesBtn = document.getElementById('showFavouritesBtn');
    const closeFavouritesBtn = document.getElementById('closeFavouritesBtn');
    const favouritesPanel = document.getElementById('favourites');

    showFavouritesBtn.addEventListener('click', () => {
        favouritesPanel.classList.add('active');
    });
    closeFavouritesBtn.addEventListener('click', () => {
        favouritesPanel.classList.remove('active');
    });

    // Obsługa przycisków tygodniowych i dodawania eventów
    document.getElementById("prevWeekBtn").addEventListener("click", () => shiftWeek(-1));
    document.getElementById("nextWeekBtn").addEventListener("click", () => shiftWeek(1));
    document.getElementById("addEventBtn").addEventListener("click", addEvent);

    // Na start rysujemy bieżący tydzień
    renderWeek();
    highlightToday();

    // Dodawanie do ulubionych
    // Nasłuch na przycisk "Dodaj do ulubionych"
    document.getElementById('addFavourtiesBtn').addEventListener('click', addFavourite);

    // Wczytaj listę z localStorage i od razu wyświetl
    refreshFavouritesList();

    // Podpowiedzi do wyszukiwania
    const filters = ['wydzial', 'wykladowca', 'sala', 'przedmiot', 'grupa', 'forma', 'typStudiow', 'semestrStudiow', 'rokStudiow'];
    filters.forEach(filterId => {
        const input = document.getElementById(filterId);
        const suggestionsBox = document.createElement('div');
        suggestionsBox.classList.add('suggestions-box');
        input.parentNode.appendChild(suggestionsBox);

        input.addEventListener('input', function () {
            const query = this.value;
            const filter = filterId;
            if (query.length > 2) {
                fetch(`/assets/scripts/SearchPredictions.php?query=${encodeURIComponent(query)}&filter=${encodeURIComponent(filter)}`)
                    .then(response => response.text())
                    .then(text => {
                        console.log(text); // Log the response text for debugging
                        return JSON.parse(text); // Parse the response text as JSON
                    })
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        data.forEach(item => {
                            const suggestionItem = document.createElement('div');
                            suggestionItem.classList.add('suggestion-item');
                            suggestionItem.textContent = item;
                            suggestionItem.addEventListener('click', function () {
                                input.value = item;
                                suggestionsBox.innerHTML = '';
                            });
                            suggestionsBox.appendChild(suggestionItem);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error); // Log any errors
                    });
            } else {
                suggestionsBox.innerHTML = '';
            }
        });
    });

    // Obsługa przycisku "Zastosuj filtry"
    document.querySelector('.filter-buttons button[type="button"]').addEventListener('click', applyFilters);

    //POBIERANIE FILTRÓW
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById("prevWeekBtn").addEventListener("click", () => shiftWeek(-1))
        {
            const filterWydział = document.getElementById('wydzial').value;
            const filterWykladowca = document.getElementById('wykladowca').value;
            const filterSala = document.getElementById('sala').value;
            const filterPrzedmiot = document.getElementById('przedmiot').value;
            const filterGrupa = document.getElementById('grupa').value;
            const filterForma = document.getElementById('forma').value;
            const filterTypStudiow = document.getElementById('typStudiow').value;
            const filterSemestrStudiow = document.getElementById('semestrStudiow').value;
            const filterRokStudiow = document.getElementById('rokStudiow').value;
        }
    });
});
function addFavourite() {
    // Najpierw pobieramy wartości z pól formularza
    const filterWydzial = document.getElementById('wydzial').value;
    const filterWykladowca = document.getElementById('wykladowca').value;
    const filterSala = document.getElementById('sala').value;
    const filterPrzedmiot = document.getElementById('przedmiot').value;
    const filterGrupa = document.getElementById('grupa').value;
    const filterForma = document.getElementById('forma').value;
    const filterTypStudiow = document.getElementById('typStudiow').value;
    const filterSemestrStudiow = document.getElementById('semestrStudiow').value;
    const filterRokStudiow = document.getElementById('rokStudiow').value;

    // Pytamy użytkownika o nazwę planu
    const userInput = prompt("Dodaj nazwę planu:");

    // Jeśli użytkownik anuluje prompt lub nie poda nazwy, wyjdź z funkcji
    if (!userInput) return;

    // Tworzymy obiekt, który będzie przechowywał nazwę planu i wszystkie filtry
    const newFavorite = {
        name: userInput,
        wydzial: filterWydzial,
        wykladowca: filterWykladowca,
        sala: filterSala,
        przedmiot: filterPrzedmiot,
        grupa: filterGrupa,
        forma: filterForma,
        typStudiow: filterTypStudiow,
        semestrStudiow: filterSemestrStudiow,
        rokStudiow: filterRokStudiow
    };

    // Pobieramy aktualną listę ulubionych z localStorage (lub pustą tablicę jeśli brak)
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    // Dodajemy nowy obiekt do tablicy favorites
    favorites.push(newFavorite);

    // Zapisujemy zaktualizowaną tablicę do localStorage
    localStorage.setItem('favorites', JSON.stringify(favorites));

    // Po zapisaniu – odświeżamy listę przycisków w ulubionych
    refreshFavouritesList();

    alert("Filtry zapisane w ulubionych!");
}
function refreshFavouritesList() {
    // 1. Odczytujemy z localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    // 2. Szukamy <ul> (lub <ol>) z id="buttonList" i czyścimy jego zawartość
    const favList = document.getElementById('buttonList');
    favList.innerHTML = '';

    // 3. Iterujemy po each "favorite"
    favorites.forEach((fav, index) => {
        // Tworzymy element listy
        const listItem = document.createElement('li');

        // Tworzymy przycisk
        const newButton = document.createElement('button');
        newButton.textContent = fav.name;  // nazwa planu

        // Po kliknięciu wczytujemy zapamiętane filtry do formularza
        newButton.addEventListener('click', () => {
            document.getElementById('wydzial').value = fav.wydzial;
            document.getElementById('wykladowca').value = fav.wykladowca;
            document.getElementById('sala').value = fav.sala;
            document.getElementById('przedmiot').value = fav.przedmiot;
            document.getElementById('grupa').value = fav.grupa;
            document.getElementById('forma').value = fav.forma;
            document.getElementById('typStudiow').value = fav.typStudiow;
            document.getElementById('semestrStudiow').value = fav.semestrStudiow;
            document.getElementById('rokStudiow').value = fav.rokStudiow;

            // Tutaj ewentualnie możemy wywołać funkcję, która przeładuje plan w tabeli
            // np. loadPlan();
        });

        // Dodajemy przycisk do listy
        listItem.appendChild(newButton);
        favList.appendChild(listItem);
    });
}
function shareSchedule() {
    const filters = {
        wydzial: document.getElementById('wydzial').value,
        wykladowca: document.getElementById('wykladowca').value,
        sala: document.getElementById('sala').value,
        przedmiot: document.getElementById('przedmiot').value,
        grupa: document.getElementById('grupa').value,
        forma: document.getElementById('forma').value,
        typStudiow: document.getElementById('typStudiow').value,
        semestrStudiow: document.getElementById('semestrStudiow').value,
        rokStudiow: document.getElementById('rokStudiow').value
    };

    const queryString = new URLSearchParams(filters).toString();
    const shareUrl = `${window.location.origin}${window.location.pathname}?${queryString}`;
    prompt("Skopiuj ten URL, aby podzielić się planem lekcji:", shareUrl);
}
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);

    document.getElementById('wydzial').value = urlParams.get('wydzial') || '';
    document.getElementById('wykladowca').value = urlParams.get('wykladowca') || '';
    document.getElementById('sala').value = urlParams.get('sala') || '';
    document.getElementById('przedmiot').value = urlParams.get('przedmiot') || '';
    document.getElementById('grupa').value = urlParams.get('grupa') || '';
    document.getElementById('forma').value = urlParams.get('forma') || '';
    document.getElementById('typStudiow').value = urlParams.get('typStudiow') || '';
    document.getElementById('semestrStudiow').value = urlParams.get('semestrStudiow') || '';
    document.getElementById('rokStudiow').value = urlParams.get('rokStudiow') || '';

    // Opcjonalnie: automatycznie zastosuj filtry po załadowaniu
    applyFilters();
});
function applyFilters() {
    const filters = {
        wydzial: document.getElementById('wydzial').value,
        wykladowca: document.getElementById('wykladowca').value,
        sala: document.getElementById('sala').value,
        przedmiot: document.getElementById('przedmiot').value,
        grupa: document.getElementById('grupa').value,
        forma: document.getElementById('forma').value,
        typStudiow: document.getElementById('typStudiow').value,
        semestrStudiow: document.getElementById('semestrStudiow').value,
        rokStudiow: document.getElementById('rokStudiow').value
    };

    const queryString = new URLSearchParams(filters).toString();

    fetch(`/assets/scripts/FiltersLogic.php?${queryString}`)
        .then(response => response.json())
        .then(data => {
            console.log('Returned data:', data); // Log the returned data
            renderWeek(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
//DZIELENIE PLANEM

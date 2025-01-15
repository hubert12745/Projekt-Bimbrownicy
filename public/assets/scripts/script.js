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


/****************************
 RENDEROWANIE TYGODNIA
 ****************************/

function renderWeek() {
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
    events.forEach(ev => {
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

document.addEventListener('DOMContentLoaded', () => {
    //Filtry
    const showFiltersBtn = document.getElementById('showFiltersBtn');
    const closeFiltersBtn = document.getElementById('closeFiltersBtn');
    const filtersPanel = document.getElementById('filters');


    // Po kliknięciu "Pokaż filtry" -> dodajemy .active (panel zasłoni sidebar)
    showFiltersBtn.addEventListener('click', () => {
        filtersPanel.classList.add('active');
    });

    // Po kliknięciu "Ukryj filtry" -> usuwamy .active
    closeFiltersBtn.addEventListener('click', () => {
        filtersPanel.classList.remove('active');
    });



    //Ulubione
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

    //Dodawanie do ulubionych
    const favList = document.getElementById('buttonList');
    const addFavoriteBtn = document.getElementById('addFavourtiesBtn');
    addFavoriteBtn.addEventListener('click', () => {

        const userInput = prompt("Dodaj nazwę planu:");
        const newButton = document.createElement('button');
        newButton.textContent = userInput;

        // Dodanie przycisku do listy
        const listItem = document.createElement('li');
        listItem.appendChild(newButton);
        favList.appendChild(listItem);

        // Zapisanie do localStorage
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        favorites.push(userInput);
        localStorage.setItem('favorites', JSON.stringify(favorites));

        alert("Filtry zapisane w ulubionych!");



    });
    // Ładowanie przycisków z localStorage przy starcie
    const savedFavorites = JSON.parse(localStorage.getItem('favorites')) || [];
    savedFavorites.forEach(fav => {
        const newButton = document.createElement('button');
        newButton.textContent = fav;
        const listItem = document.createElement('li');
        listItem.appendChild(newButton);
        favList.appendChild(listItem);
    });
});
// Przycisk do wyświetlania aktualnego tygodnia
document.getElementById('showCurrentWeekBtn').addEventListener('click', () => {
    currentMonday = getMondayOfCurrentWeek(new Date());
    renderWeek();
    highlightToday(); // <- ponowne wywołanie
});

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

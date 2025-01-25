/***********************************************
 * ZMIENNE GLOBALNE I DANE
 ***********************************************/

// Który widok jest aktualnie wybrany: 'week', 'month', 'day', 'semester'
let currentView = 'week';

// Dzień/tydzień/miesiąc:
let currentMonday = getMondayOfCurrentWeek(new Date()); // Początek tygodnia
let currentMonth = new Date();                          // do renderMonth
let currentDay = new Date();                            // do renderDay

// ---------- SEMESTR ---------- //
// Rok akademicki, np. 2025 -> semestr zimowy 2025/2026
let currentSemesterYear = 2025;
// 'winter' = paź-list-gru-sty (od października bieżącego roku do stycznia roku+1)
// 'summer' = luty-mar-kwi-maj-cze (tego samego roku, co w currentSemesterYear)
let currentSemester = 'winter';

// Główna tablica eventów — pusta na start (filtrowane z bazy)
let events = [];

/***********************************************
 * FUNKCJE POMOCNICZE
 ***********************************************/

/** Zwraca Date poniedziałku tygodnia, w którym jest `date`. */
function getMondayOfCurrentWeek(date) {
    const newDate = new Date(date);
    const day = newDate.getDay(); // 0..6 (niedziela=0, poniedziałek=1, ...)
    const diff = (day === 0) ? -6 : (1 - day);
    newDate.setDate(newDate.getDate() + diff);
    newDate.setHours(0,0,0,0);
    return newDate;
}

/** Format krótkiego nagłówka dnia (np. "pon (20.03)") */
function formatDayHeader(dateObj, dayIndex) {
    const dayNames = ["pon", "wt", "śr", "czw", "pt", "sob", "nd"];
    const dd = String(dateObj.getDate()).padStart(2, '0');
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    return `${dayNames[dayIndex]} (${dd}.${mm})`;
}

/** Nazwa miesiąca po polsku. */
function getMonthName(monthIndex) {
    const monthNames = [
        "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
        "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"
    ];
    return monthNames[monthIndex] || "";
}

/***********************************************
 * FUNKCJA PRZYDZIAŁU "LANES" (obok siebie)
 ***********************************************/
/**
 * Dla widoku tygodnia, dnia i semestru – żeby wydarzenia nakładające się
 * były rysowane obok siebie zamiast jedne na drugich.
 *
 * Sortuje wydarzenia (zakładamy, że już są posortowane po starcie)
 * i przydziela je do ścieżek tak, by dwa nakładające się eventy
 * nie były w tej samej ścieżce (lane).
 *
 * Zwraca obiekt { assignments, laneCount }
 *  assignments = tablica obiektów { event, lane }
 *  laneCount   = liczba ścieżek
 */
function assignEventLanes(dayEvents) {
    const lanes = [];
    const assignments = [];

    for (let ev of dayEvents) {
        const [startH, startM] = ev.start.split(":").map(Number);
        const [endH, endM] = ev.end.split(":").map(Number);
        const evStart = startH * 60 + startM;
        const evEnd   = endH * 60 + endM;

        let placedLane = -1;
        // Szukamy wolnej ścieżki
        for (let i = 0; i < lanes.length; i++) {
            const lastEventInLane = lanes[i][lanes[i].length - 1];
            const [lendH, lendM] = lastEventInLane.end.split(":").map(Number);
            const laneLastEnd = lendH * 60 + lendM;

            if (laneLastEnd <= evStart) {
                placedLane = i;
                break;
            }
        }

        // Jeśli brak wolnej -> tworzymy nową
        if (placedLane === -1) {
            placedLane = lanes.length;
            lanes.push([]);
        }

        lanes[placedLane].push(ev);
        assignments.push({ event: ev, lane: placedLane });
    }

    return {
        assignments,
        laneCount: lanes.length
    };
}

/***********************************************
 * RENDEROWANIE TYGODNIA
 ***********************************************/
function buildScheduleBody() {
    const tbody = document.getElementById("schedule-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    for (let hour = 7; hour <= 19; hour++) {
        const tr = document.createElement("tr");

        // Pierwsza kolumna z godziną
        const hourTd = document.createElement("td");
        hourTd.textContent = String(hour).padStart(2, '0');
        tr.appendChild(hourTd);

        // 7 kolumn (pon..nd)
        for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
            const td = document.createElement("td");
            td.id = `day${dayIndex}-hour${hour}`;
            td.style.position = "relative"; // dla absolute eventów
            tr.appendChild(td);
        }
        tbody.appendChild(tr);
    }
}

function highlightToday() {
    // Usuwamy stare podświetlenia
    for (let i = 0; i < 7; i++) {
        const dayHeader = document.getElementById(`day-${i}`);
        if (dayHeader) dayHeader.classList.remove('todayHighlight');

        for (let hour = 7; hour <= 19; hour++) {
            const cell = document.getElementById(`day${i}-hour${hour}`);
            if (cell) cell.classList.remove('todayHighlight');
        }
    }

    // Sprawdzamy, czy 'today' mieści się w aktualnym tygodniu
    const today = new Date();
    today.setHours(0,0,0,0);

    const diffDays = (today - currentMonday) / (1000*60*60*24);
    if (diffDays >= 0 && diffDays < 7) {
        const dayIndex = Math.floor(diffDays);

        const dayHeader = document.getElementById(`day-${dayIndex}`);
        if (dayHeader) dayHeader.classList.add('todayHighlight');

        for (let hour = 7; hour <= 19; hour++) {
            const cell = document.getElementById(`day${dayIndex}-hour${hour}`);
            if (cell) cell.classList.add('todayHighlight');
        }
    }
}

function renderWeek(filteredEvents = events) {
    buildScheduleBody();

    // Ustaw nagłówki dni
    for (let i = 0; i < 7; i++) {
        const dayHeader = document.getElementById(`day-${i}`);
        if (!dayHeader) continue;
        const thisDay = new Date(currentMonday);
        thisDay.setDate(currentMonday.getDate() + i);
        dayHeader.textContent = formatDayHeader(thisDay, i);
    }

    // Grupujemy wydarzenia: osobno dla każdego dnia (0..6)
    const eventsByDay = [[], [], [], [], [], [], []];

    filteredEvents.forEach(ev => {
        const evDate = new Date(ev.date);
        const diffDays = (evDate - currentMonday) / (1000*60*60*24);
        if (diffDays >= 0 && diffDays < 7) {
            const dayIndex = Math.floor(diffDays);
            eventsByDay[dayIndex].push(ev);
        }
    });

    // Dla każdego dnia sortujemy, przydzielamy lane, rysujemy
    for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
        const dayEvents = eventsByDay[dayIndex];
        // sort po starcie
        dayEvents.sort((a, b) => {
            const [aH,aM] = a.start.split(":").map(Number);
            const [bH,bM] = b.start.split(":").map(Number);
            return (aH*60 + aM) - (bH*60 + bM);
        });
        const { assignments, laneCount } = assignEventLanes(dayEvents);
        // Rysujemy
        assignments.forEach(({event, lane}) => {
            drawEventInCellsWithLane(dayIndex, event, lane, laneCount);
        });
    }
}

function drawEventInCellsWithLane(dayIndex, event, lane, laneCount) {
    const [startHour, startMin] = event.start.split(":").map(Number);
    const [endHour, endMin] = event.end.split(":").map(Number);

    const startTotal = startHour*60 + startMin;
    const endTotal   = endHour*60 + endMin;

    // Komórka bazowa = wiersz startHour
    const cellId = `day${dayIndex}-hour${startHour}`;
    const td = document.getElementById(cellId);
    if (!td) return;

    const rowHeight = 60; // 1 godz. = 60px
    const offsetTop = (startMin / 60) * rowHeight;
    const duration  = endTotal - startTotal;
    const blockHeight = (duration / 60) * rowHeight;

    // Obliczamy szerokość i przesunięcie w poziomie
    const laneWidthPercent = 100 / laneCount;
    const leftPercent = laneWidthPercent * lane;

    // Stworzenie diva
    const div = document.createElement("div");
    div.classList.add("event-block", event.type);
    div.style.position = "absolute";
    div.style.top = offsetTop + "px";
    div.style.height = blockHeight + "px";
    div.style.left = leftPercent + "%";
    div.style.width = laneWidthPercent + "%";
    div.title = `Tytuł: ${event.title}\nGodziny: ${event.start} - ${event.end}\nTyp: ${event.type}`;

    // Dodatkowe elementy
    const titleDiv = document.createElement("div");
    titleDiv.classList.add("event-title");
    titleDiv.textContent = event.title;

    const timeDiv = document.createElement("div");
    timeDiv.classList.add("event-time");
    timeDiv.textContent = `${event.start} - ${event.end}`;

    div.appendChild(titleDiv);
    div.appendChild(timeDiv);

    td.appendChild(div);
}

function shiftWeek(n) {
    currentMonday.setDate(currentMonday.getDate() + 7*n);
    renderWeek();
    highlightToday();
}

function addEvent() {
    const dayIndex = prompt("Dzień tygodnia (0=pon, ... 6=nd):");
    if (dayIndex === null) return;

    const start = prompt("Start (HH:MM):");
    if (!start) return;

    const end = prompt("Koniec (HH:MM):");
    if (!end) return;

    const title = prompt("Tytuł zajęć:");
    if (!title) return;

    const type = prompt("Typ (lab, wyklad, projekt...):") || "wyklad";

    const d = new Date(currentMonday);
    d.setDate(d.getDate() + parseInt(dayIndex));
    const yyyy = d.getFullYear();
    const mm   = String(d.getMonth()+1).padStart(2,'0');
    const dd   = String(d.getDate()).padStart(2,'0');

    events.push({
        date: `${yyyy}-${mm}-${dd}`,
        start,
        end,
        type,
        title
    });

    renderAccordingToCurrentView();
}

/***********************************************
 * RENDEROWANIE MIESIĄCA (STARY SPOSÓB)
 ***********************************************/
function renderMonth(filteredEvents = events) {
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();

    const monthNameElem = document.getElementById('monthName');
    if (monthNameElem) {
        monthNameElem.textContent = `${getMonthName(month)} ${year}`;
    }

    const monthBody = document.getElementById("month-body");
    if (!monthBody) return;
    monthBody.innerHTML = "";

    const firstDay = new Date(year, month, 1);
    const daysInMonth = new Date(year, month+1, 0).getDate();

    let startDay = firstDay.getDay();
    if (startDay === 0) startDay = 7;
    const offset = startDay - 1;
    const totalCells = offset + daysInMonth;
    const rows = Math.ceil(totalCells / 7);

    let dayCounter = 1;
    for (let row = 0; row < rows; row++) {
        const tr = document.createElement("tr");
        for (let col = 0; col < 7; col++) {
            const td = document.createElement("td");
            const cellIndex = row*7 + col;

            if (cellIndex >= offset && dayCounter <= daysInMonth) {
                const dayNumber = dayCounter;
                td.innerHTML = `<div class="day-number">${dayNumber}</div>`;
                const cellDate = new Date(year, month, dayNumber);
                drawEventsInMonthCell(td, cellDate, filteredEvents);
                dayCounter++;
            } else {
                td.classList.add("inactive");
            }
            tr.appendChild(td);
        }
        monthBody.appendChild(tr);
    }
}

/**
 * Stary sposób: pionowa lista wydarzeń w komórce, + link 'więcej'
 * bez rozkładania eventów obok siebie.
 */
function drawEventsInMonthCell(td, cellDate, eventsArray) {
    const y = cellDate.getFullYear();
    const m = String(cellDate.getMonth()+1).padStart(2,'0');
    const d = String(cellDate.getDate()).padStart(2,'0');
    const cellDateStr = `${y}-${m}-${d}`;

    const dayEvents = eventsArray.filter(ev => ev.date === cellDateStr);
    if (dayEvents.length === 0) return;

    const eventsContainer = document.createElement('div');
    eventsContainer.classList.add('month-events-container');

    const MAX_VISIBLE = 2;
    dayEvents.slice(0, MAX_VISIBLE).forEach(ev => {
        const evDiv = document.createElement('div');
        evDiv.classList.add('event-item', ev.type);

        // Tytuł + godzina
        const titleDiv = document.createElement("div");
        titleDiv.classList.add("event-title");
        titleDiv.textContent = ev.title;

        const timeDiv = document.createElement("div");
        timeDiv.classList.add("event-time");
        timeDiv.textContent = ev.start;

        evDiv.appendChild(titleDiv);
        evDiv.appendChild(timeDiv);

        evDiv.title = `Tytuł: ${ev.title}\nGodziny: ${ev.start} - ${ev.end}\nTyp: ${ev.type}`;
        eventsContainer.appendChild(evDiv);
    });

    if (dayEvents.length > MAX_VISIBLE) {
        const hiddenCount = dayEvents.length - MAX_VISIBLE;
        const moreLink = document.createElement('div');
        moreLink.classList.add('more-events-link');
        moreLink.textContent = `+${hiddenCount} więcej`;
        eventsContainer.appendChild(moreLink);

        const extraContainer = document.createElement('div');
        extraContainer.classList.add('extra-events');
        extraContainer.style.display = 'none';

        dayEvents.slice(MAX_VISIBLE).forEach(ev => {
            const evDiv = document.createElement('div');
            evDiv.classList.add('event-item', ev.type);

            const titleDiv = document.createElement("div");
            titleDiv.classList.add("event-title");
            titleDiv.textContent = ev.title;

            const timeDiv = document.createElement("div");
            timeDiv.classList.add("event-time");
            timeDiv.textContent = ev.start;

            evDiv.appendChild(titleDiv);
            evDiv.appendChild(timeDiv);

            evDiv.title = `Tytuł: ${ev.title}\nGodziny: ${ev.start} - ${ev.end}\nTyp: ${ev.type}`;
            extraContainer.appendChild(evDiv);
        });

        eventsContainer.appendChild(extraContainer);
        moreLink.addEventListener('click', () => {
            if (extraContainer.style.display === 'none') {
                extraContainer.style.display = 'block';
                moreLink.textContent = 'Ukryj';
            } else {
                extraContainer.style.display = 'none';
                moreLink.textContent = `+${hiddenCount} więcej`;
            }
        });
    }

    td.appendChild(eventsContainer);
}

/***********************************************
 * RENDEROWANIE DNIA (Z LANE)
 ***********************************************/
function renderDay(filteredEvents = events) {
    const dayTbody = document.getElementById("day-schedule-body");
    if (!dayTbody) return;
    dayTbody.innerHTML = "";

    const dayHeader = document.getElementById("day-view-header");
    if (dayHeader) {
        const dd = String(currentDay.getDate()).padStart(2, '0');
        const mm = String(currentDay.getMonth()+1).padStart(2, '0');
        const yyyy = currentDay.getFullYear();
        dayHeader.textContent = `Dzień (${dd}.${mm}.${yyyy})`;
    }

    // godziny 7..19
    for (let hour = 7; hour <= 19; hour++) {
        const tr = document.createElement("tr");

        const hourTd = document.createElement("td");
        hourTd.textContent = String(hour).padStart(2, '0');
        tr.appendChild(hourTd);

        const eventTd = document.createElement("td");
        eventTd.id = `day-${hour}`;
        eventTd.style.position = "relative";
        eventTd.style.overflow = "visible";
        tr.appendChild(eventTd);

        dayTbody.appendChild(tr);
    }

    // Wstaw eventy
    const y = currentDay.getFullYear();
    const m = String(currentDay.getMonth()+1).padStart(2,'0');
    const d = String(currentDay.getDate()).padStart(2,'0');
    const dayStr = `${y}-${m}-${d}`;

    const dayEvents = filteredEvents.filter(ev => ev.date === dayStr);
    // sortujemy
    dayEvents.sort((a,b) => {
        const [aH,aM] = a.start.split(":").map(Number);
        const [bH,bM] = b.start.split(":").map(Number);
        return (aH*60 + aM) - (bH*60 + bM);
    });

    // lane
    const { assignments, laneCount } = assignEventLanes(dayEvents);

    // rysujemy
    assignments.forEach(({event, lane}) => {
        drawDayEventWithLane(event, lane, laneCount);
    });
}

function drawDayEventWithLane(event, lane, laneCount) {
    const [startHour, startMin] = event.start.split(":").map(Number);
    const [endHour, endMin] = event.end.split(":").map(Number);

    const startTotal = startHour*60 + startMin;
    const endTotal   = endHour*60 + endMin;
    const td = document.getElementById(`day-${startHour}`);
    if (!td) return;

    const rowHeight = 60;
    const offsetTop = (startMin/60)*rowHeight;
    const duration  = endTotal - startTotal;
    const blockHeight = (duration/60)*rowHeight;

    // szerokość i przesunięcie
    const laneWidth = 100 / laneCount;
    const leftOffset = laneWidth * lane;

    const div = document.createElement("div");
    div.classList.add("event-block", event.type);
    div.title = `Tytuł: ${event.title}\nGodziny: ${event.start} - ${event.end}\nTyp: ${event.type}`;

    div.style.position = "absolute";
    div.style.top = offsetTop + "px";
    div.style.height = blockHeight + "px";
    div.style.left = leftOffset + "%";
    div.style.width = laneWidth + "%";

    const titleDiv = document.createElement("div");
    titleDiv.classList.add("event-title");
    titleDiv.textContent = event.title;

    const timeDiv = document.createElement("div");
    timeDiv.classList.add("event-time");
    timeDiv.textContent = `${event.start} - ${event.end}`;

    div.appendChild(titleDiv);
    div.appendChild(timeDiv);

    td.appendChild(div);
}

function shiftDay(n) {
    currentDay.setDate(currentDay.getDate() + n);
    renderDay();
    highlightTodayDay();
}

function highlightTodayDay() {
    const dayTbody = document.getElementById("day-schedule-body");
    if (!dayTbody) return;

    [...dayTbody.querySelectorAll('td')].forEach(td => td.classList.remove('todayHighlight'));

    const today = new Date();
    today.setHours(0,0,0,0);
    const dayOnly = new Date(currentDay);
    dayOnly.setHours(0,0,0,0);

    if (dayOnly.getTime() === today.getTime()) {
        [...dayTbody.querySelectorAll('td')].forEach(td => td.classList.add('todayHighlight'));
    }
}

/***********************************************
 * RENDEROWANIE SEMESTRU (Z LANE)
 ***********************************************/

/**
 * Funkcja zwraca listę obiektów {year, month}, określających które
 * miesiące należą do obecnego semestru 'winter' / 'summer'
 * w roku akademickim `currentSemesterYear`.
 *
 *  - Zimowy: paź (9), lis (10), gru (11) danego roku + styczeń (0) kolejnego.
 *  - Letni: luty(1)..czerwiec(5) TEGO SAMEGO roku,
 *    żeby np. semestr letni 2025 pokazywał luty–czerwiec 2025.
 */
function getMonthsForCurrentSemester() {
    if (currentSemester === 'winter') {
        // paź, lis, gru => currentSemesterYear
        // styczeń => currentSemesterYear + 1
        return [
            { year: currentSemesterYear,   month: 9 },
            { year: currentSemesterYear,   month: 10 },
            { year: currentSemesterYear,   month: 11 },
            { year: currentSemesterYear+1, month: 0 },
        ];
    } else {
        // 'summer': luty..czerwiec TEGO SAMEGO roku
        return [
            { year: currentSemesterYear, month: 1 },
            { year: currentSemesterYear, month: 2 },
            { year: currentSemesterYear, month: 3 },
            { year: currentSemesterYear, month: 4 },
            { year: currentSemesterYear, month: 5 },
        ];
    }
}

/** Renderuje cały widok semestralny (kilka "mini-miesięcy") */
function renderSemester(filteredEvents = events) {
    const semesterContainer = document.getElementById('semester-container');
    if (!semesterContainer) return;
    semesterContainer.innerHTML = '';

    // Pobieramy listę miesięcy
    const months = getMonthsForCurrentSemester();

    months.forEach(({year, month}) => {
        const block = renderOneSemesterMonth(year, month, filteredEvents);
        semesterContainer.appendChild(block);
    });
}

/** Renderuje pojedynczy "mini-miesiąc" w semestrze. */
function renderOneSemesterMonth(year, month, eventsArray) {
    const firstDayOfMonth = new Date(year, month, 1);

    // Kontener
    const block = document.createElement('div');
    block.classList.add('semester-month-block');

    // Nagłówek np. "Październik 2025"
    const header = document.createElement('div');
    header.classList.add('semester-month-header');
    header.textContent = `${getMonthName(month)} ${year}`;
    block.appendChild(header);

    // Tabela
    const table = document.createElement('table');
    table.classList.add('semester-month-table');

    // Thead z dniami tygodnia
    const thead = document.createElement('thead');
    const row = document.createElement('tr');
    const dayNames = ["Pon", "Wt", "Śr", "Czw", "Pt", "Sob", "Nd"];
    dayNames.forEach(dn => {
        const th = document.createElement('th');
        th.textContent = dn;
        row.appendChild(th);
    });
    thead.appendChild(row);
    table.appendChild(thead);

    // Tbody
    const tbody = document.createElement('tbody');
    const daysInMonth = new Date(year, month+1, 0).getDate();

    let startDay = firstDayOfMonth.getDay();
    if (startDay === 0) startDay = 7;
    const offset = startDay - 1;
    const totalCells = offset + daysInMonth;
    const rows = Math.ceil(totalCells / 7);

    let dayCounter = 1;
    for (let r = 0; r < rows; r++) {
        const tr = document.createElement('tr');
        for (let c = 0; c < 7; c++) {
            const td = document.createElement('td');
            td.style.position = "relative";
            td.style.height = "80px";  // np. 80px, aby zmieścić eventy
            td.style.overflow = "hidden";
            const cellIndex = r*7 + c;

            if (cellIndex >= offset && dayCounter <= daysInMonth) {
                const thisDay = dayCounter;
                // Numer dnia
                const dayDiv = document.createElement('div');
                dayDiv.classList.add('day-number');
                dayDiv.textContent = thisDay;
                td.appendChild(dayDiv);

                const cellDate = new Date(year, month, thisDay);

                // Podświetlenie "dzisiaj"
                const today = new Date();
                today.setHours(0,0,0,0);
                if (cellDate.toDateString() === today.toDateString()) {
                    td.classList.add('todayHighlight');
                }

                // Wstaw eventy
                drawSemesterEventsInCell(td, cellDate, eventsArray);

                dayCounter++;
            } else {
                td.classList.add('inactive');
            }
            tr.appendChild(td);
        }
        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    block.appendChild(table);
    return block;
}

/**
 * Wstawia wydarzenia do komórki w semestrze z rozkładaniem obok siebie
 * (podobnie jak w widoku tygodnia/dnia).
 */
function drawSemesterEventsInCell(td, cellDate, eventsArray) {
    const y = cellDate.getFullYear();
    const m = String(cellDate.getMonth()+1).padStart(2,'0');
    const d = String(cellDate.getDate()).padStart(2,'0');
    const cellDateStr = `${y}-${m}-${d}`;

    const dayEvents = eventsArray.filter(ev => ev.date === cellDateStr);
    if (dayEvents.length === 0) return;

    // sort
    dayEvents.sort((a,b) => {
        const [aH,aM] = a.start.split(":").map(Number);
        const [bH,bM] = b.start.split(":").map(Number);
        return (aH*60 + aM) - (bH*60 + bM);
    });

    const { assignments, laneCount } = assignEventLanes(dayEvents);

    // rysujemy w 80px wysokości, 24h
    const cellHeight = parseFloat(td.style.height) || 80;
    const minutesInDay = 24*60;
    const pxPerMin = cellHeight / minutesInDay;

    assignments.forEach(({event, lane}) => {
        const [startH, startM] = event.start.split(":").map(Number);
        const [endH, endM] = event.end.split(":").map(Number);
        const startTotal = startH*60 + startM;
        const endTotal   = endH*60 + endM;
        const duration   = endTotal - startTotal;

        const offsetTop = startTotal * pxPerMin;
        const blockHeight = duration * pxPerMin;

        const laneWidth = 100 / laneCount;
        const leftOffset = laneWidth * lane;

        const div = document.createElement('div');
        div.classList.add('semester-event-item', event.type);
        div.style.position = "absolute";
        div.style.top = offsetTop + "px";
        div.style.height = blockHeight + "px";
        div.style.left = leftOffset + "%";
        div.style.width = laneWidth + "%";
        div.title = `Tytuł: ${event.title}\nGodziny: ${event.start} - ${event.end}\nTyp: ${event.type}`;

        // Prosta zawartość
        const titleDiv = document.createElement("div");
        titleDiv.classList.add("event-title");
        titleDiv.textContent = event.title;
        titleDiv.style.fontSize = "10px";

        const timeDiv = document.createElement("div");
        timeDiv.classList.add("event-time");
        timeDiv.textContent = event.start;
        timeDiv.style.fontSize = "10px";

        div.appendChild(titleDiv);
        div.appendChild(timeDiv);

        td.appendChild(div);
    });
}

/**
 * shiftSemester(+1) => przejście do następnego semestru
 * shiftSemester(-1) => poprzedniego
 */
function shiftSemester(direction) {
    if (currentSemester === 'winter') {
        if (direction === 1) {
            // z zimowego 2025 do letniego 2025
            currentSemester = 'summer';
        } else {
            // z zimowego 2025 do letniego 2024
            currentSemester = 'summer';
            currentSemesterYear -= 1;
        }
    } else {
        // 'summer'
        if (direction === 1) {
            // z letniego 2025 do zimowego 2026
            currentSemester = 'winter';
            currentSemesterYear += 1;
        } else {
            // z letniego 2025 do zimowego 2025
            currentSemester = 'winter';
        }
    }
    renderSemester();
}

/***********************************************
 * OBSŁUGA PRZYCISKÓW, FILTRÓW, ULUBIONYCH, ETC.
 ***********************************************/

/** Zastosuj filtry (przykładowy mechanizm) */
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
            console.log('Returned data:', data);

            // Adaptacja danych z bazy do formatu eventów
            const adaptedEvents = data.map(lesson => {
                const startDate = new Date(lesson.lesson_start);
                const endDate = new Date(lesson.lesson_end);

                const year  = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day   = String(startDate.getDate()).padStart(2, '0');

                const startH = String(startDate.getHours()).padStart(2, '0');
                const startM = String(startDate.getMinutes()).padStart(2, '0');
                const endH   = String(endDate.getHours()).padStart(2, '0');
                const endM   = String(endDate.getMinutes()).padStart(2, '0');

                return {
                    date:  `${year}-${month}-${day}`,
                    start: `${startH}:${startM}`,
                    end:   `${endH}:${endM}`,
                    title: lesson.lesson_description,
                    type:  lesson.lesson_form
                };
            });

            // Nadpisanie globalnej zmiennej "events"
            events = adaptedEvents;
            renderAccordingToCurrentView();
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function renderAccordingToCurrentView() {
    switch (currentView) {
        case 'week':
            renderWeek();
            highlightToday();
            break;
        case 'month':
            renderMonth();
            break;
        case 'day':
            renderDay();
            highlightTodayDay();
            break;
        case 'semester':
            renderSemester();
            break;
    }
}

/** Walidacje: np. brak cyfr, tylko liczby, itd. */
function validateStringNoNumbers(value) {
    if (/\d/.test(value)) {
        return `Numbers are not allowed.`;
    }
    return '';
}
function validateIntegerOnly(value) {
    if (value && !/^\d+$/.test(value)) {
        return `Please enter a valid integer.`;
    }
    return '';
}

/** Wyświetlanie komunikatów obok inputa */
function showValidationMessage(input, message) {
    let messageElement = input.nextElementSibling;
    if (!messageElement || !messageElement.classList.contains('validation-message')) {
        messageElement = document.createElement('div');
        messageElement.classList.add('validation-message');
        input.parentNode.insertBefore(messageElement, input.nextSibling);
    }
    messageElement.textContent = message;
}

/** Sprawdzenie wszystkich pól i włączanie/wyłączanie przycisku */
function checkValidations() {
    const filtersToValidate = [
        { id: 'wydzial', validate: validateStringNoNumbers },
        { id: 'wykladowca', validate: validateStringNoNumbers },
        { id: 'forma', validate: validateStringNoNumbers },
        { id: 'typStudiow', validate: validateStringNoNumbers },
        { id: 'semestrStudiow', validate: validateIntegerOnly },
        { id: 'rokStudiow', validate: validateIntegerOnly }
    ];

    let allValid = true;
    filtersToValidate.forEach(filter => {
        const input = document.getElementById(filter.id);
        if (!input) return;
        const message = filter.validate(input.value);
        showValidationMessage(input, message);
        if (message) {
            allValid = false;
        }
    });

    const button = document.querySelector('.filter-buttons button[type="button"]');
    if (button) button.disabled = !allValid;
}

/** Dodawanie aktualnych filtrów do ulubionych */
function addFavourite() {
    const getVal = id => document.getElementById(id)?.value || '';

    const newFavorite = {
        name: prompt("Dodaj nazwę planu:") || '',
        wydzial: getVal('wydzial'),
        wykladowca: getVal('wykladowca'),
        sala: getVal('sala'),
        przedmiot: getVal('przedmiot'),
        grupa: getVal('grupa'),
        forma: getVal('forma'),
        typStudiow: getVal('typStudiow'),
        semestrStudiow: getVal('semestrStudiow'),
        rokStudiow: getVal('rokStudiow')
    };

    if (!newFavorite.name) return;

    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    favorites.push(newFavorite);
    localStorage.setItem('favorites', JSON.stringify(favorites));

    refreshFavouritesList();
    alert("Filtry zapisane w ulubionych!");
}

/**
 * Wczytanie ulubionych do listy przycisków.
 * Po kliknięciu:
 *  - ustawiamy wartości w inputach
 *  - natychmiast wywołujemy applyFilters()
 */
function refreshFavouritesList() {
    const favList = document.getElementById('buttonList');
    if (!favList) return;
    favList.innerHTML = '';

    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    favorites.forEach(fav => {
        const listItem = document.createElement('li');
        const newButton = document.createElement('button');
        newButton.textContent = fav.name;

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

            // Po wybraniu ulubionych - od razu zastosuj filtry
            applyFilters();
        });

        listItem.appendChild(newButton);
        favList.appendChild(listItem);
    });
}

/** Generowanie linku do udostępnienia */
function shareSchedule() {
    const filters = {
        wydzial: document.getElementById('wydzial')?.value || '',
        wykladowca: document.getElementById('wykladowca')?.value || '',
        sala: document.getElementById('sala')?.value || '',
        przedmiot: document.getElementById('przedmiot')?.value || '',
        grupa: document.getElementById('grupa')?.value || '',
        forma: document.getElementById('forma')?.value || '',
        typStudiow: document.getElementById('typStudiow')?.value || '',
        semestrStudiow: document.getElementById('semestrStudiow')?.value || '',
        rokStudiow: document.getElementById('rokStudiow')?.value || ''
    };

    const queryString = new URLSearchParams(filters).toString();
    const shareUrl = `${window.location.origin}${window.location.pathname}?${queryString}`;
    prompt("Skopiuj ten URL, aby podzielić się planem:", shareUrl);
}

/**
 * Reset filtrów:
 *  - Czyści wszystkie inputy
 *  - Czyści tablicę events
 *  - Przerysowuje kalendarz (pusty)
 */
function resetFilters() {
    // Lista wszystkich pól do wyczyszczenia
    const filterIds = [
        'wydzial', 'wykladowca', 'sala', 'przedmiot',
        'grupa', 'forma', 'typStudiow', 'semestrStudiow', 'rokStudiow'
    ];

    // Czyszczenie pól
    filterIds.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.value = '';
        }
    });

    // Wyczyszczenie globalnej listy wydarzeń
    events = [];

    // Przerysowanie kalendarza (bez wydarzeń)
    renderAccordingToCurrentView();
}

/***********************************************
 * GŁÓWNY DOMContentLoaded
 ***********************************************/
document.addEventListener('DOMContentLoaded', () => {
    // Walidacje w czasie rzeczywistym
    const filtersToValidate = [
        { id: 'wydzial', validate: validateStringNoNumbers },
        { id: 'wykladowca', validate: validateStringNoNumbers },
        { id: 'forma', validate: validateStringNoNumbers },
        { id: 'typStudiow', validate: validateStringNoNumbers },
        { id: 'semestrStudiow', validate: validateIntegerOnly },
        { id: 'rokStudiow', validate: validateIntegerOnly }
    ];
    filtersToValidate.forEach(f => {
        const input = document.getElementById(f.id);
        if (!input) return;
        input.addEventListener('input', () => {
            const msg = f.validate(input.value);
            showValidationMessage(input, msg);
            checkValidations();
        });
    });
    checkValidations();

    // Panel Filtry (pokaż/ukryj)
    const showFiltersBtn = document.getElementById('showFiltersBtn');
    const closeFiltersBtn = document.getElementById('closeFiltersBtn');
    const filtersPanel = document.getElementById('filters');
    if (showFiltersBtn && closeFiltersBtn && filtersPanel) {
        showFiltersBtn.addEventListener('click', () => filtersPanel.classList.add('active'));
        closeFiltersBtn.addEventListener('click', () => filtersPanel.classList.remove('active'));
    }

    // Panel Ulubione (pokaż/ukryj)
    const showFavouritesBtn = document.getElementById('showFavouritesBtn');
    const closeFavouritesBtn = document.getElementById('closeFavouritesBtn');
    const favouritesPanel = document.getElementById('favourites');
    if (showFavouritesBtn && closeFavouritesBtn && favouritesPanel) {
        showFavouritesBtn.addEventListener('click', () => favouritesPanel.classList.add('active'));
        closeFavouritesBtn.addEventListener('click', () => favouritesPanel.classList.remove('active'));
    }

    // Przycisk "Zastosuj Filtry"
    const applyFiltersBtn = document.querySelector('.filter-buttons button[type="button"]');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyFilters);
    }

    // Przycisk "Reset filtrów"
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetFilters);
    }

    // Wyszukiwarki (dla sugestii)
    const filterIds = [
        'wydzial','wykladowca','sala','przedmiot','grupa',
        'forma','typStudiow','semestrStudiow','rokStudiow'
    ];
    filterIds.forEach(filterId => {
        const input = document.getElementById(filterId);
        if (!input) return;
        const suggestionsBox = document.createElement('div');
        suggestionsBox.classList.add('suggestions-box');
        input.parentNode.appendChild(suggestionsBox);

        input.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                fetch(`/assets/scripts/SearchPredictions.php?query=${encodeURIComponent(query)}&filter=${encodeURIComponent(filterId)}`)
                    .then(resp => resp.text())
                    .then(text => JSON.parse(text))
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        data.forEach(item => {
                            const suggestionItem = document.createElement('div');
                            suggestionItem.classList.add('suggestion-item');
                            suggestionItem.textContent = item;
                            suggestionItem.addEventListener('click', () => {
                                input.value = item;
                                suggestionsBox.innerHTML = '';
                            });
                            suggestionsBox.appendChild(suggestionItem);
                        });
                    })
                    .catch(err => console.error(err));
            } else {
                suggestionsBox.innerHTML = '';
            }
        });
    });

    // Przełączniki widoków
    const viewMonthBtn = document.getElementById('viewMonthBtn');
    const viewWeekBtn = document.getElementById('viewWeekBtn');
    const viewDayBtn = document.getElementById('viewDayBtn');
    const viewSemesterBtn = document.getElementById('viewSemesterBtn');

    if (viewMonthBtn) {
        viewMonthBtn.addEventListener('click', () => {
            document.getElementById('weekView').style.display = 'none';
            document.getElementById('dayView').style.display = 'none';
            document.getElementById('semesterView').style.display = 'none';
            document.getElementById('monthView').style.display = 'block';

            currentView = 'month';
            renderMonth();
        });
    }
    if (viewWeekBtn) {
        viewWeekBtn.addEventListener('click', () => {
            document.getElementById('monthView').style.display = 'none';
            document.getElementById('dayView').style.display = 'none';
            document.getElementById('semesterView').style.display = 'none';
            document.getElementById('weekView').style.display = 'block';

            currentView = 'week';
            renderWeek();
            highlightToday();
        });
    }
    if (viewDayBtn) {
        viewDayBtn.addEventListener('click', () => {
            document.getElementById('weekView').style.display = 'none';
            document.getElementById('monthView').style.display = 'none';
            document.getElementById('semesterView').style.display = 'none';
            document.getElementById('dayView').style.display = 'block';

            currentView = 'day';
            renderDay();
            highlightTodayDay();
        });
    }
    if (viewSemesterBtn) {
        viewSemesterBtn.addEventListener('click', () => {
            document.getElementById('weekView').style.display = 'none';
            document.getElementById('monthView').style.display = 'none';
            document.getElementById('dayView').style.display = 'none';
            document.getElementById('semesterView').style.display = 'block';

            currentView = 'semester';
            renderSemester();
        });
    }

    // Obsługa przycisków "Poprzedni" / "Następny" / "Dodaj zajęcia"
    const prevWeekBtn = document.getElementById("prevWeekBtn");
    const nextWeekBtn = document.getElementById("nextWeekBtn");
    const addEventBtn = document.getElementById("addEventBtn");

    if (prevWeekBtn) {
        prevWeekBtn.addEventListener("click", () => {
            if (currentView === 'week') {
                shiftWeek(-1);
            } else if (currentView === 'month') {
                currentMonth.setMonth(currentMonth.getMonth() - 1);
                renderMonth();
            } else if (currentView === 'day') {
                shiftDay(-1);
            } else if (currentView === 'semester') {
                shiftSemester(-1);
            }
        });
    }
    if (nextWeekBtn) {
        nextWeekBtn.addEventListener("click", () => {
            if (currentView === 'week') {
                shiftWeek(1);
            } else if (currentView === 'month') {
                currentMonth.setMonth(currentMonth.getMonth() + 1);
                renderMonth();
            } else if (currentView === 'day') {
                shiftDay(1);
            } else if (currentView === 'semester') {
                shiftSemester(1);
            }
        });
    }
    if (addEventBtn) {
        addEventBtn.addEventListener("click", addEvent);
    }

    // Przycisk "Dodaj do ulubionych"
    const addFavourtiesBtn = document.getElementById('addFavourtiesBtn');
    if (addFavourtiesBtn) {
        addFavourtiesBtn.addEventListener('click', addFavourite);
    }

    // Lista ulubionych
    refreshFavouritesList();

    // "Pokaż bieżący tydzień / miesiąc / dzień / semestr"
    const showCurrentWeekBtn = document.getElementById('showCurrentWeekBtn');
    if (showCurrentWeekBtn) {
        showCurrentWeekBtn.addEventListener('click', () => {
            if (currentView === 'month') {
                currentMonth = new Date();
                renderMonth();
            } else if (currentView === 'day') {
                currentDay = new Date();
                renderDay();
                highlightTodayDay();
            } else if (currentView === 'semester') {
                currentSemesterYear = 2025;
                currentSemester = 'winter';
                renderSemester();
            } else {
                // Tydzień
                currentMonday = getMondayOfCurrentWeek(new Date());
                renderWeek();
                highlightToday();
            }
        });
    }

    // "Pokaż tydzień" z datePicker (lub dzień, semestr, zależnie od widoku)
    const changeWeekBtn = document.getElementById('changeWeekBtn');
    if (changeWeekBtn) {
        changeWeekBtn.addEventListener('click', () => {
            const weekPicker = document.getElementById('weekPicker')?.value;
            if (!weekPicker) {
                alert("Proszę wybrać datę.");
                return;
            }
            const selectedDate = new Date(weekPicker);

            if (currentView === 'month') {
                currentMonth.setFullYear(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
                renderMonth();
            } else if (currentView === 'day') {
                currentDay = selectedDate;
                renderDay();
                highlightTodayDay();
            } else if (currentView === 'semester') {
                // Przykładowo wymuszenie semestru zimowego i roku na podstawie daty:
                currentSemesterYear = selectedDate.getFullYear();
                currentSemester = 'winter';
                renderSemester();
            } else {
                currentMonday = getMondayOfCurrentWeek(selectedDate);
                renderWeek();
                highlightToday();
            }
        });
    }

    // Wczytanie filtrów z URL (jeśli ktoś użyje linku z parametrami)
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

    // Na starcie: widok tygodniowy
    renderWeek();
    highlightToday();
});

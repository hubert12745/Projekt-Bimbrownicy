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
let currentSemesterYear = 2025;
let currentSemester = 'winter';

let events = [];

/***********************************************
 * FUNKCJE POMOCNICZE
 ***********************************************/

/** Zwraca Date poniedziałku tygodnia, w którym jest `date`. */
function getMondayOfCurrentWeek(date) {
    const newDate = new Date(date);
    const day = newDate.getDay();

    // Poniedziałek: diff= (1 - 1)=0  / Niedziela: diff= (1 - 0)=1?
    // Lepiej uwzględnić, że Sunday=0 => chcemy -6, by cofnąć do poniedziałku
    const diff = (day === 0) ? -6 : (1 - day);
    newDate.setDate(newDate.getDate() + diff);
    newDate.setHours(0,0,0,0);
    return newDate;
}

/** Nazwa miesiąca po polsku. */
function getMonthName(monthIndex) {
    const monthNames = [
        "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
        "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"
    ];
    return monthNames[monthIndex] || "";
}

/** Format krótkiego nagłówka dnia (np. "pon (20.03)") */
function formatDayHeader(dateObj, dayIndex) {
    const dayNames = ["pon", "wt", "śr", "czw", "pt", "sob", "nd"];
    const dd = String(dateObj.getDate()).padStart(2, '0');
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    return `${dayNames[dayIndex]} (${dd}.${mm})`;
}

/***********************************************
 * FUNKCJA PRZYDZIAŁU "LANES" (obok siebie)
 * (wykorzystywane w widoku tygodnia i dnia)
 ***********************************************/
function assignEventLanes(dayEvents) {
    // W tym algorytmie sortujemy eventy
    // i staramy się upchnąć je w "lane", by eventy nakładające się
    // nie były w jednej kolumnie.

    const lanes = [];
    const assignments = [];


    for (let ev of dayEvents) {
        const [startH, startM] = ev.start.split(":").map(Number);
        const [endH, endM] = ev.end.split(":").map(Number);
        const evStart = startH * 60 + startM;
        const evEnd   = endH * 60 + endM;

        let placedLane = -1;
        for (let i = 0; i < lanes.length; i++) {
            const lastEventInLane = lanes[i][lanes[i].length - 1];
            const [lendH, lendM] = lastEventInLane.end.split(":").map(Number);
            const laneLastEnd = lendH * 60 + lendM;

            // Jeśli to wydarzenie zaczyna się po (lub równo) zakończeniu
            // ostatniego w danej lane => można tam wstawić
            if (laneLastEnd <= evStart) {
                placedLane = i;
                break;
            }
        }

        // Jeśli nie znaleziono wolnej "lane"
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

    // Godziny 7..19 (możesz zmienić zakres)
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
            td.style.position = "relative";
            tr.appendChild(td);
        }
        tbody.appendChild(tr);
    }
}

function highlightToday() {
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

    // Grupujemy wydarzenia: osobno dla każdego dnia 0..6
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
        dayEvents.sort((a, b) => {
            const [aH,aM] = a.start.split(":").map(Number);
            const [bH,bM] = b.start.split(":").map(Number);
            return (aH*60 + aM) - (bH*60 + bM);
        });

        const { assignments, laneCount } = assignEventLanes(dayEvents);
        assignments.forEach(({ event, lane }) => {
            drawEventInCellsWithLane(dayIndex, event, lane, laneCount);
        });
    }
}

function drawEventInCellsWithLane(dayIndex, event, lane, laneCount) {
    const [startHour, startMin] = event.start.split(":").map(Number);
    const [endHour, endMin] = event.end.split(":").map(Number);

    const startTotal = startHour*60 + startMin;
    const endTotal   = endHour*60 + endMin;

    const cellId = `day${dayIndex}-hour${startHour}`;
    const td = document.getElementById(cellId);
    if (!td) return;

    const rowHeight = 60; // 1 godz. = 60px
    const offsetTop = (startMin / 60) * rowHeight;
    const duration  = endTotal - startTotal;
    const blockHeight = (duration / 60) * rowHeight;

    // obliczamy szerokość
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

    // Treść w środku
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
 * RENDEROWANIE MIESIĄCA
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
 * Funkcja używana w widoku "miesiąc", ale też ją użyjemy w semestrze.
 * Wypełnia komórkę listą eventów (z linkiem "+x więcej").
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
 * RENDEROWANIE DNIA
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

    for (let hour = 7; hour <= 19; hour++) {
        const tr = document.createElement("tr");

        const hourTd = document.createElement("td");
        hourTd.textContent = String(hour).padStart(2, '0');
        tr.appendChild(hourTd);

        const eventTd = document.createElement("td");
        eventTd.id = `day-${hour}`;
        eventTd.style.position = "relative";
        tr.appendChild(eventTd);

        dayTbody.appendChild(tr);
    }

    // Filtrujemy eventy na ten konkretny dzień
    const y = currentDay.getFullYear();
    const m = String(currentDay.getMonth()+1).padStart(2,'0');
    const d = String(currentDay.getDate()).padStart(2,'0');
    const dayStr = `${y}-${m}-${d}`;

    const dayEvents = filteredEvents.filter(ev => ev.date === dayStr);
    dayEvents.sort((a,b) => {
        const [aH,aM] = a.start.split(":").map(Number);
        const [bH,bM] = b.start.split(":").map(Number);
        return (aH*60 + aM) - (bH*60 + bM);
    });

    const { assignments, laneCount } = assignEventLanes(dayEvents);

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
 * WIDOK SEMESTRALNY (jako kilka miesięcy obok siebie)
 ***********************************************/

/**
 * Zwraca listę obiektów {year, month}, określających miesiące w obecnym semestrze.
 * np. dla semestru zimowego i currentSemesterYear=2025 => paź, lis, gru (2025), sty (2025)
 */
function getMonthsForCurrentSemester() {
    if (currentSemester === 'winter') {
        return [
            { year: currentSemesterYear, month: 9 },  // paź
            { year: currentSemesterYear, month: 10 }, // lis
            { year: currentSemesterYear, month: 11 }, // gru
            // Jeżeli chcesz styczeń w roku +1 => użyj: { year: currentSemesterYear + 1, month: 0 }
            { year: currentSemesterYear, month: 0 }   // sty
        ];
    } else {
        // 'summer': luty..czerwiec
        return [
            { year: currentSemesterYear, month: 1 },  // luty
            { year: currentSemesterYear, month: 2 },  // marzec
            { year: currentSemesterYear, month: 3 },  // kwiecień
            { year: currentSemesterYear, month: 4 },  // maj
            { year: currentSemesterYear, month: 5 },  // czerwiec
        ];
    }
}

/**
 * Nowy widok semestralny = kilka "mini-widoków miesięcznych" obok siebie
 * z taką samą logiką rysowania eventów jak w "renderMonth".
 */
function renderSemesterAsMonths(filteredEvents = events) {
    const semesterContainer = document.getElementById('semester-container');
    if (!semesterContainer) return;
    semesterContainer.innerHTML = '';

    // Lista miesięcy w semestrze
    const months = getMonthsForCurrentSemester();

    // (Możesz też ustawić w CSS: .semester-container { display: flex; gap: 20px; }
    semesterContainer.style.display = 'flex';
    semesterContainer.style.gap = '20px';

    months.forEach(({ year, month }) => {
        // Główny blok
        const block = document.createElement('div');
        block.classList.add('semester-month-block');

        // Tytuł miesiąca, np. "Październik 2025"
        const heading = document.createElement('h3');
        heading.textContent = `${getMonthName(month)} ${year}`;
        block.appendChild(heading);

        // Tabelka identyczna jak w "renderMonth"
        const table = document.createElement('table');
        table.classList.add('month-table');

        // Thead
        const thead = document.createElement('thead');
        const daysRow = document.createElement('tr');
        const dayNames = ["Pon", "Wt", "Śr", "Czw", "Pt", "Sob", "Nd"];
        dayNames.forEach(dn => {
            const th = document.createElement('th');
            th.textContent = dn;
            daysRow.appendChild(th);
        });
        thead.appendChild(daysRow);
        table.appendChild(thead);

        // Tbody
        const tbody = document.createElement('tbody');
        table.appendChild(tbody);

        // Liczymy dni w miesiącu
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month+1, 0).getDate();

        let startDay = firstDay.getDay();
        if (startDay === 0) startDay = 7; // w JS niedziela=0
        const offset = startDay - 1;
        const totalCells = offset + daysInMonth;
        const rows = Math.ceil(totalCells / 7);

        let dayCounter = 1;
        for (let r = 0; r < rows; r++) {
            const tr = document.createElement('tr');
            for (let c = 0; c < 7; c++) {
                const td = document.createElement('td');
                const cellIndex = r*7 + c;

                if (cellIndex >= offset && dayCounter <= daysInMonth) {
                    const dayNumber = dayCounter;

                    // Numer dnia
                    const dayDiv = document.createElement('div');
                    dayDiv.classList.add('day-number');
                    dayDiv.textContent = dayNumber;
                    td.appendChild(dayDiv);

                    const cellDate = new Date(year, month, dayNumber);
                    drawEventsInMonthCell(td, cellDate, filteredEvents);

                    dayCounter++;
                } else {
                    td.classList.add('inactive');
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }

        block.appendChild(table);
        semesterContainer.appendChild(block);
    });
}

/**
 * shiftSemester(+1) => przejście do następnego semestru
 * shiftSemester(-1) => poprzedniego semestru
 * (możesz dostosować do własnych potrzeb)
 */
function shiftSemester(direction) {
    if (currentSemester === 'winter') {
        if (direction === 1) {
            // z zimowego do letniego
            currentSemester = 'summer';
        } else {
            // z zimowego do letniego poprzedniego roku
            currentSemester = 'summer';
            currentSemesterYear -= 1;
        }
    } else {
        // 'summer'
        if (direction === 1) {
            // z letniego do zimowego następnego roku
            currentSemester = 'winter';
            currentSemesterYear += 1;
        } else {
            // z letniego do zimowego tego samego roku
            currentSemester = 'winter';
        }
    }
    renderSemesterAsMonths();
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
        rokStudiow: document.getElementById('rokStudiow').value,
        nrAlbumu: document.getElementById('nrAlbumu').value
    };

    const queryString = new URLSearchParams(filters).toString();
    console.log("Requesting: /index.php?action=schedule-filter&" + queryString);
    fetch(`/index.php?action=schedule-filter&${queryString}`)
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
            renderSemesterAsMonths();
            break;
    }
}

/** Walidacje proste */
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
        rokStudiow: getVal('rokStudiow'),
        nrAlbumu: getVal('nrAlbumu')
    };

    if (!newFavorite.name) return;

    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    favorites.push(newFavorite);
    localStorage.setItem('favorites', JSON.stringify(favorites));

    refreshFavouritesList();
    alert("Filtry zapisane w ulubionych!");
}

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
            document.getElementById('nrAlbumu').value = fav.nrAlbumu;

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

/** Reset filtrów */
function resetFilters() {
    const filterIds = [
        'wydzial', 'wykladowca', 'sala', 'przedmiot',
        'grupa', 'nrAlbumu', 'forma', 'typStudiow', 'semestrStudiow', 'rokStudiow'
    ];

    filterIds.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.value = '';
        }
    });

    events = [];
    renderAccordingToCurrentView();
}

/***********************************************
 * GŁÓWNY DOMContentLoaded
 ***********************************************/
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburgerBtn');

    // Obsługa otwierania/zamykania sidebaru
    hamburgerBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener('click', (event) => {
        if (!sidebar.contains(event.target) && event.target !== hamburgerBtn) {
            sidebar.classList.remove('active');
        }
    });

    // Obsługa rozwijanej grupy przycisków
    const toggleBtn = document.getElementById("toggle-btn");
    const buttonGroup = document.querySelector(".button-group");

    toggleBtn.addEventListener("click", function (event) {
        if (buttonGroup.style.display === "none" || buttonGroup.style.display === "") {
            buttonGroup.style.display = "flex";
        } else {
            buttonGroup.style.display = "none";
        }
        event.stopPropagation(); // Zapobiega zamknięciu od razu po kliknięciu
    });

    document.addEventListener("click", function (event) {
        if (!buttonGroup.contains(event.target) && event.target !== toggleBtn) {
            buttonGroup.style.display = "none";
        }
    });

    /***********************************************
     * FUNKCJE DOSTĘPNOŚCI
     ***********************************************/
    const contrastButton = document.getElementById("toggle-contrast");
    const fontSizeButton = document.getElementById("toggle-font-size");

    // Obsługa trybu wysokiego kontrastu
    contrastButton.addEventListener("click", function () {
        document.body.classList.toggle("high-contrast");

        // Zapamiętanie ustawienia w localStorage
        if (document.body.classList.contains("high-contrast")) {
            localStorage.setItem("highContrast", "enabled");
        } else {
            localStorage.removeItem("highContrast");
        }
    });

    // Obsługa powiększania czcionki
    fontSizeButton.addEventListener("click", function () {
        document.body.classList.toggle("large-font");

        // Zapamiętanie ustawienia w localStorage
        if (document.body.classList.contains("large-font")) {
            localStorage.setItem("largeFont", "enabled");
        } else {
            localStorage.removeItem("largeFont");
        }
    });

    // Przywrócenie ustawień użytkownika po odświeżeniu strony
    if (localStorage.getItem("highContrast") === "enabled") {
        document.body.classList.add("high-contrast");
    }
    if (localStorage.getItem("largeFont") === "enabled") {
        document.body.classList.add("large-font");
    }

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

    const filterIds = [
        'wydzial','wykladowca','sala','przedmiot','grupa',
        'forma','typStudiow','semestrStudiow','rokStudiow'
    ];

    // Inputy z sugestiami
    filterIds.forEach(filterId => {
        const input = document.getElementById(filterId);
        if (!input) return;

        const suggestionsBox = document.createElement('div');
        suggestionsBox.classList.add('suggestions-box');
        input.parentNode.appendChild(suggestionsBox);

        input.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                fetch(`/index.php?action=search-predictions&query=${encodeURIComponent(query)}&filter=${encodeURIComponent(filterId)}`)
                    .then(resp => {
                        if (!resp.ok) throw new Error(`Błąd: ${resp.status} ${resp.statusText}`);
                        return resp.json();
                    })
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
            renderSemesterAsMonths();
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
                // Ustal semestr na "zimowy" i rok na obecny itp. – dowolna logika
                currentSemester = 'winter';
                currentSemesterYear = new Date().getFullYear();
                renderSemesterAsMonths();
            } else {
                // Tydzień
                currentMonday = getMondayOfCurrentWeek(new Date());
                renderWeek();
                highlightToday();
            }
        });
    }

    // "Pokaż tydzień" z datePicker (lub dzień, semestr, w zależności od currentView)
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
                // Przykładowo: semestr zimowy w roku wybranej daty (możesz zmodyfikować)
                currentSemester = 'winter';
                currentSemesterYear = selectedDate.getFullYear();
                renderSemesterAsMonths();
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
    document.getElementById('nrAlbumu').value = urlParams.get('nrAlbumu') || '';
    document.getElementById('forma').value = urlParams.get('forma') || '';
    document.getElementById('typStudiow').value = urlParams.get('typStudiow') || '';
    document.getElementById('semestrStudiow').value = urlParams.get('semestrStudiow') || '';
    document.getElementById('rokStudiow').value = urlParams.get('rokStudiow') || '';

    // Na starcie: widok tygodniowy
    renderWeek();
    highlightToday();
});
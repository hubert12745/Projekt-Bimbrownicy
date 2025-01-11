DROP TABLE Faculty;
DROP TABLE Worker;
DROP TABLE Subject;
DROP TABLE Room;
DROP TABLE ClassGroup;
DROP TABLE Lesson;
DROP TABLE Student;
DROP TABLE StudentGroup;

CREATE TABLE Faculty
(
    faculty_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    faculty_name  TEXT NOT NULL UNIQUE ,
    faculty_short TEXT NOT NULL UNIQUE
);

CREATE TABLE Worker
(
    worker_id  INTEGER PRIMARY KEY AUTOINCREMENT,
    title      TEXT NOT NULL,
    first_name TEXT NOT NULL ,
    last_name  TEXT NOT NULL,
    full_name  TEXT NOT NULL,
    login      TEXT NOT NULL UNIQUE,
    faculty_id INTEGER NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE Subject
(
    subject_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_name TEXT NOT NULL,
    subject_type TEXT NOT NULL,
    faculty_id   INTEGER NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)

);

CREATE TABLE Room
(
    room_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name  TEXT NOT NULL UNIQUE,
    faculty_id INTEGER NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE ClassGroup
(
    group_id       INTEGER PRIMARY KEY AUTOINCREMENT,
    group_name     TEXT NOT NULL UNIQUE,
    semester       INTEGER NOT NULL,
    faculty_id     INTEGER NOT NULL,
    department     TEXT NOT NULL,
    field_of_study TEXT NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE Lesson
(
    lesson_id           INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id          INTEGER NOT NULL,
    worker_id           INTEGER NOT NULL,
    group_id            INTEGER NOT NULL,
    room_id             INTEGER NOT NULL,
    lesson_form         TEXT NOT NULL,
    lesson_form_short   TEXT NOT NULL,
    lesson_status       TEXT NOT NULL,
    lesson_status_short TEXT NOT NULL,
    lesson_start        TEXT NOT NULL,
    lesson_end          TEXT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES Subject (subject_id),
    FOREIGN KEY (worker_id) REFERENCES Worker (worker_id),
    FOREIGN KEY (group_id) REFERENCES ClassGroup (group_id),
    FOREIGN KEY (room_id) REFERENCES Room (room_id)
);

CREATE TABLE Student
(
    student_id INTEGER PRIMARY KEY
);

CREATE TABLE StudentGroup
(
    student_id INTEGER NOT NULL,
    group_id   INTEGER NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Student (student_id),
    FOREIGN KEY (group_id) REFERENCES ClassGroup (group_id)
);

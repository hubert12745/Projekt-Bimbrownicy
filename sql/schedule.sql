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
    faculty_name  TEXT  UNIQUE ,
    faculty_short TEXT  UNIQUE
);

CREATE TABLE Worker
(
    worker_id  INTEGER PRIMARY KEY AUTOINCREMENT,
    title      TEXT ,
    first_name TEXT  ,
    last_name  TEXT ,
    full_name  TEXT ,
    login      TEXT  UNIQUE,
    faculty_id INTEGER ,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE Subject
(
    subject_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_name TEXT  UNIQUE,
    subject_type TEXT ,
    faculty_id   INTEGER ,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)

);

CREATE TABLE Room
(
    room_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name  TEXT  UNIQUE,
    faculty_id INTEGER ,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE ClassGroup
(
    group_id       INTEGER PRIMARY KEY AUTOINCREMENT,
    group_name     TEXT  UNIQUE,
    semester       INTEGER ,
    year           INTEGER ,
    faculty_id     INTEGER ,
    department     TEXT ,
    field_of_study TEXT ,
    type_of_study  TEXT ,
    FOREIGN KEY (faculty_id) REFERENCES Faculty (faculty_id)
);

CREATE TABLE Lesson
(
    lesson_id           INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id          INTEGER ,
    worker_id           INTEGER ,
    group_id            INTEGER ,
    room_id             INTEGER ,
    lesson_description  TEXT ,
    lesson_form         TEXT ,
    lesson_form_short   TEXT ,
    lesson_status       TEXT ,
    lesson_start        TEXT ,
    lesson_end          TEXT ,
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
    student_id INTEGER ,
    group_id   INTEGER ,
    PRIMARY KEY (student_id, group_id),
    FOREIGN KEY (student_id) REFERENCES Student (student_id),
    FOREIGN KEY (group_id) REFERENCES ClassGroup (group_id)
);

DROP TABLE IF EXISTS Wydzial;
DROP TABLE IF EXISTS Sala_z_budynkiem;
DROP TABLE IF EXISTS Tok_studiow;
DROP TABLE IF EXISTS Przedmiot;
DROP TABLE IF EXISTS Grupa;
DROP TABLE IF EXISTS Student;
DROP TABLE IF EXISTS Grupa_Student;
DROP TABLE IF EXISTS Wykladowca;
DROP TABLE IF EXISTS Zajecia;

CREATE TABLE Wydzial (
                         id INTEGER PRIMARY KEY AUTOINCREMENT,
                         nazwa TEXT NOT NULL,
                         sk TEXT NOT NULL
);

CREATE TABLE Sala_z_budynkiem (
                                  id INTEGER PRIMARY KEY AUTOINCREMENT,
                                  budynek_sala TEXT NOT NULL,
                                  wydzial_id INTEGER NOT NULL,
                                  FOREIGN KEY (wydzial_id) REFERENCES Wydzial(id)
);

CREATE TABLE Tok_studiow (
                             id INTEGER PRIMARY KEY AUTOINCREMENT,
                             typ TEXT NOT NULL,
                             tryb TEXT NOT NULL,
                             typ_sk TEXT NOT NULL,
                             tryb_sk TEXT NOT NULL
);

CREATE TABLE Przedmiot (
                           id INTEGER PRIMARY KEY AUTOINCREMENT,
                           nazwa TEXT NOT NULL,
                           tok_studiow_id INTEGER NOT NULL,
                           forma TEXT NOT NULL,
                           semestr INTEGER NOT NULL,
                           rok INTEGER NOT NULL,
                           FOREIGN KEY (tok_studiow_id) REFERENCES Tok_studiow(id)
);

CREATE TABLE Grupa (
                       id INTEGER PRIMARY KEY AUTOINCREMENT,
                       nazwa TEXT NOT NULL
);

CREATE TABLE Student (
                         id INTEGER PRIMARY KEY
);

CREATE TABLE Grupa_Student (
                               grupa_id INTEGER NOT NULL,
                               student_id INTEGER NOT NULL,
                               FOREIGN KEY (grupa_id) REFERENCES Grupa(id),
                               FOREIGN KEY (student_id) REFERENCES Student(id)
);

CREATE TABLE Wykladowca (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            nazwisko_imie TEXT NOT NULL,
                            tytul TEXT NOT NULL
);

CREATE TABLE Zajecia (
                         id INTEGER PRIMARY KEY,
                         data_start TEXT NOT NULL,
                         data_koniec TEXT NOT NULL,
                         zastepca TEXT,
                         wykladowca_id INTEGER NOT NULL,
                         wydzial_id INTEGER NOT NULL,
                         grupa_id INTEGER NOT NULL,
                         tok_studiow_id INTEGER NOT NULL,
                         sala_id INTEGER NOT NULL,
                         przedmiot_id INTEGER NOT NULL,
                         semestr INTEGER NOT NULL,
                         FOREIGN KEY (wykladowca_id) REFERENCES Wykladowca(id),
                         FOREIGN KEY (wydzial_id) REFERENCES Wydzial(id),
                         FOREIGN KEY (grupa_id) REFERENCES Grupa(id),
                         FOREIGN KEY (tok_studiow_id) REFERENCES Tok_studiow(id),
                         FOREIGN KEY (sala_id) REFERENCES Sala_z_budynkiem(id),
                         FOREIGN KEY (przedmiot_id) REFERENCES Przedmiot(id)
);

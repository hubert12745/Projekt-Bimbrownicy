<?php
namespace App\Service;
use App\Service\Config;

class ScrapeData
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function scrapeData(string $department){
        $apiURL = "https://plan.zut.edu.pl/schedule_student.php?kind=apiwi&department={$department}&start=2024-10-7&end=2024-10-8";
        $response = file_get_contents($apiURL);
        if(!$response) {
            die('No response');
        }
        $count = 0;
        $json = json_decode($response, true);

        foreach ($json as $item) {
            $count++;
            if (isset($item['wydzial']) && isset($item['wydz_sk'])) {
                $this->insertWydzial($item['wydzial'], $item['wydz_sk']);
            }
            if(isset($item['room'])){
                $this->insertSalaBudynek($item['room'], $item['wydzial']);
            }
            if(isset($item['typ_sk']) && isset($item['rodzaj_sk'])){
                $this->insertTokStudiow($item['typ_sk'], $item['rodzaj_sk'], $item['rodzaj'], $item['typ']);
            }
            if(isset($item['subject'])){
                $forma = $item['forma'] ?? 'Brak';
                $semestr = $item['semestr'] ?? 0;
                $rok = $item['rok'] ?? 0;
                $this->insertPrzedmiot($item['subject'], $item['rodzaj_sk'], $item['typ_sk'], $forma, $semestr, $rok);
            }
            if(isset($item['group_name'])){
                $this->insertGrupa($item['group_name']);
            }
            if(isset($item['worker'])){
                $tytul = $item['tytul'] ?? 'Brak';
                $this->insertWykladowca($item['worker'], $tytul);
            }
            if(isset($item['id'])){
                $this->insertZajecia($item);
            }
        }
    }

    private function insertWydzial(string $wydzial, string $wydz_sk)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Wydzial (nazwa, sk) VALUES (:wydzial, :wydz_sk)");
        $stmt->execute([':wydzial' => $wydzial, ':wydz_sk' => $wydz_sk]);
    }

    private function insertSalaBudynek(string $room, string $wydzial)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Sala_z_budynkiem (budynek_sala, wydzial_id) SELECT :room, id FROM Wydzial WHERE nazwa = :wydzial");
        $stmt->execute([':room' => $room, ':wydzial' => $wydzial]);
    }

    private function insertTokStudiow(string $typ_sk, string $tryb_sk, string $tryb, string $typ)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Tok_studiow (typ, tryb, typ_sk, tryb_sk) VALUES (:typ, :tryb, :typ_sk, :tryb_sk)");
        $stmt->execute([':typ' => $typ, ':tryb' => $tryb, ':typ_sk' => $typ_sk, ':tryb_sk' => $tryb_sk]);
    }

    private function insertPrzedmiot(string $przedmiot, string $tryb, string $typ, string $forma, int $semestr, int $rok)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Przedmiot (nazwa, tok_studiow_id, forma, semestr, rok) VALUES (:przedmiot, (SELECT id FROM Tok_studiow WHERE typ_sk = :typ AND tryb_sk = :tryb), :forma, :semestr, :rok)");
        $stmt->execute([':przedmiot' => $przedmiot, ':typ' => $typ, ':tryb' => $tryb, ':forma' => $forma, ':semestr' => $semestr, ':rok' => $rok]);
    }

    private function insertGrupa(string $grupa)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Grupa (nazwa) VALUES (:grupa)");
        $stmt->execute([':grupa' => $grupa]);
    }

    private function insertWykladowca(string $wykladowca, string $tytul)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Wykladowca (nazwisko_imie, tytul) VALUES (:wykladowca, :tytul)");
        $stmt->execute([':wykladowca' => $wykladowca, ':tytul' => $tytul]);
    }

    private function insertZajecia(array $item)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Zajecia (id, data_start, data_koniec, zastepca, wykladowca_id, wydzial_id, grupa_id, tok_studiow_id, sala_id, przedmiot_id) VALUES (:id, :data_start, :data_koniec, :zastepca, (SELECT id FROM Wykladowca WHERE nazwisko_imie = :wykladowca), (SELECT id FROM Wydzial WHERE nazwa = :wydzial), (SELECT id FROM Grupa WHERE nazwa = :grupa), (SELECT id FROM Tok_studiow WHERE typ_sk = :tok_studiow), (SELECT id FROM Sala_z_budynkiem WHERE budynek_sala = :sala), (SELECT id FROM Przedmiot WHERE nazwa = :przedmiot))");
        $stmt->execute([
            ':id' => $item['id'],
            ':data_start' => $item['start'],
            ':data_koniec' => $item['end'],
            ':zastepca' => $item['worker_cover'] ?? 'Brak',
            ':wykladowca' => $item['worker'],
            ':wydzial' => $item['wydzial'],
            ':grupa' => $item['group_name'],
            ':tok_studiow' => $item['typ_sk'],
            ':sala' => $item['room'],
            ':przedmiot' => $item['subject']
        ]);
    }
}
<?php
namespace App\Model;
require_once __DIR__ . '/../Service/Config.php';
use PDO;
use App\Service\Config;

class Faculty
{
    private ?int $facultyId = null;
    private ?string $facultyName;
    private ?string $facultyShort;

    public function getFacultyId(): ?int
    {
        return $this->facultyId;
    }

    public function setFacultyId(?int $facultyId): Faculty
    {
        $this->facultyId = $facultyId;
        return $this;
    }

    public function getFacultyName(): ?string
    {
        return $this->facultyName;
    }

    public function setFacultyName(?string $facultyName): Faculty
    {
        $this->facultyName = $facultyName;
        return $this;
    }

    public function getFacultyShort(): ?string
    {
        return $this->facultyShort;
    }

    public function setFacultyShort(?string $facultyShort): Faculty
    {
        $this->facultyShort = $facultyShort;
        return $this;
    }
    public function fill($array): Faculty
    {
        $this->setFacultyName($array['wydzial']);
        $this->setFacultyShort($array['wydz_sk']);
        return $this;
    }

    public static function fromApi($array): Faculty
    {
        $faculty = new self();
        $faculty->fill($array);
        return $faculty;
    }

    public function save($facultyName, $facultyShort)
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO Faculty ( faculty_name, faculty_short) VALUES (:faculty_name, :faculty_short)');
        $stmt->execute([
            'faculty_name' => $facultyName,
            'faculty_short' => $facultyShort
        ]);
    }
}
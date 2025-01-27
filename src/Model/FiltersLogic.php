<?php
namespace App\Model;

use PDO;
use App\Service\Config;
use PDOException;

class FiltersLogic
{
    private static $pdo;

    public static function init()
    {
        if (!self::$pdo) {
            self::$pdo = new PDO(
                Config::get('db_dsn'),
                Config::get('db_user'),
                Config::get('db_pass')
            );
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public static function applyFilters(array $filters)
    {
        self::init();

        try {
            if ($filters['nrAlbumu']) {
                Student::checkAndInsertStudent($filters['nrAlbumu']);
            }

            $query = "SELECT * FROM Lesson WHERE 1=1";

            if ($filters['wydzial']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE faculty_id IN (SELECT faculty_id FROM Faculty WHERE faculty_name LIKE :wydzial))";
            }
            if ($filters['wykladowca']) {
                $query .= " AND worker_id IN (SELECT worker_id FROM Worker WHERE full_name LIKE :wykladowca)";
            }
            if ($filters['sala']) {
                $query .= " AND room_id IN (SELECT room_id FROM Room WHERE room_name LIKE :sala)";
            }
            if ($filters['przedmiot']) {
                $query .= " AND subject_id IN (SELECT subject_id FROM Subject WHERE subject_name LIKE :przedmiot)";
            }
            if ($filters['grupa']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE group_name LIKE :grupa)";
            }
            if ($filters['forma']) {
                $query .= " AND lesson_form LIKE :forma";
            }
            if ($filters['typStudiow']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE type_of_study LIKE :typStudiow)";
            }
            if ($filters['semestrStudiow']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE semester LIKE :semestrStudiow)";
            }
            if ($filters['rokStudiow']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE year LIKE :rokStudiow)";
            }
            if ($filters['nrAlbumu']) {
                $query .= " AND group_id IN (SELECT group_id FROM ClassGroup WHERE group_id IN (SELECT group_id FROM StudentGroup WHERE student_id LIKE :nrAlbumu))";
            }

            $stmt = self::$pdo->prepare($query);

            foreach ($filters as $key => $value) {
                if ($value) {
                    $stmt->bindValue(":$key", "%$value%");
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}
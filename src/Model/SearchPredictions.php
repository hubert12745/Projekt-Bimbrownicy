<?php

namespace App\Model;

use PDO;
use App\Service\Config;
use PDOException;

class SearchPredictions
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

    public static function getPredictions(string $query, string $filter): array
    {
        self::init();

        if (empty($query) || empty($filter)) {
            return [];
        }

        try {
            $column = '';
            $table = '';
            switch ($filter) {
                case 'wydzial':
                    $column = 'faculty_name';
                    $table = 'Faculty';
                    break;
                case 'wykladowca':
                    $column = 'full_name';
                    $table = 'Worker';
                    break;
                case 'sala':
                    $column = 'room_name';
                    $table = 'Room';
                    break;
                case 'przedmiot':
                    $column = 'subject_name';
                    $table = 'Subject';
                    break;
                case 'grupa':
                    $column = 'group_name';
                    $table = 'ClassGroup';
                    break;
                case 'forma':
                    $column = 'lesson_form';
                    $table = 'Lesson';
                    break;
                case 'typStudiow':
                    $column = 'type_of_study';
                    $table = 'ClassGroup';
                    break;
                case 'semestrStudiow':
                    $column = 'semester';
                    $table = 'ClassGroup';
                    break;
                case 'rokStudiow':
                    $column = 'year';
                    $table = 'ClassGroup';
                    break;
                default:
                    return [];
            }

            $stmt = self::$pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column LIKE :query LIMIT 10");
            $stmt->execute(['query' => '%' . $query . '%']);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);

        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}
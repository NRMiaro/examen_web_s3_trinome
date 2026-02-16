<?php

namespace app\models;

use Flight;

class VilleModel {
    
    public static function getAllVilles(): array {
        $sql = "SELECT id, nom FROM s3_ville ORDER BY nom";
        $stmt = Flight::db()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
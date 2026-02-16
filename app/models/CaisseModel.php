<?php

namespace app\models;

class CaisseModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllCaisse()
    {
        $stmt = $this->db->query("
            SELECT id, montant, date_don
            FROM v_caisse
            ORDER BY date_don DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalCaisse()
    {
        $stmt = $this->db->query("SELECT SUM(montant) as total FROM v_caisse");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function searchCaisse($search)
    {
        $searchTerm = '%' . $search . '%';
        $stmt = $this->db->prepare("
            SELECT id, montant, date_don
            FROM v_caisse
            WHERE montant LIKE :search OR date_don LIKE :search
            ORDER BY date_don DESC
        ");
        $stmt->execute([':search' => $searchTerm]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}



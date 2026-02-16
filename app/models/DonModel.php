<?php

namespace app\models;

class DonModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllDons()
    {
        $stmt = $this->db->query("
            SELECT d.id, d.date_don,
                   GROUP_CONCAT(CONCAT(b.nom, ': ', dd.quantite, ' kg') SEPARATOR ', ') as details
            FROM s3_don d
            LEFT JOIN s3_don_details dd ON d.id = dd.id_don
            LEFT JOIN s3_besoin b ON dd.id_besoin = b.id
            GROUP BY d.id
            ORDER BY d.date_don DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function searchDons($search = '', $date_debut = '', $date_fin = '')
    {
        $conditions = [];
        $params = [];
        
        // Condition de recherche textuelle
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $conditions[] = "(d.date_don LIKE :search OR b.nom LIKE :search)";
            $params[':search'] = $searchTerm;
        }
        
        // Condition de date début
        if (!empty($date_debut)) {
            $conditions[] = "DATE(d.date_don) >= :date_debut";
            $params[':date_debut'] = $date_debut;
        }
        
        // Condition de date fin
        if (!empty($date_fin)) {
            $conditions[] = "DATE(d.date_don) <= :date_fin";
            $params[':date_fin'] = $date_fin;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $stmt = $this->db->prepare("
            SELECT d.id, d.date_don,
                   GROUP_CONCAT(CONCAT(b.nom, ': ', dd.quantite, ' kg') SEPARATOR ', ') as details
            FROM s3_don d
            LEFT JOIN s3_don_details dd ON d.id = dd.id_don
            LEFT JOIN s3_besoin b ON dd.id_besoin = b.id
            {$whereClause}
            GROUP BY d.id
            ORDER BY d.date_don DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDonById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM s3_don WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getDonDetails($id_don)
    {
        $stmt = $this->db->prepare("
            SELECT dd.*, b.nom as besoin_nom
            FROM s3_don_details dd
            JOIN s3_besoin b ON dd.id_besoin = b.id
            WHERE dd.id_don = :id_don
        ");
        $stmt->execute([':id_don' => $id_don]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllBesoins()
    {
        $stmt = $this->db->query("SELECT id, nom FROM s3_besoin ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function insertDon($data)
    {
        try {
            $this->db->beginTransaction();

            // Insérer le don
            $stmt = $this->db->prepare("INSERT INTO s3_don (date_don) VALUES (:date_don)");
            $stmt->execute([':date_don' => $data['date_don']]);
            $id_don = $this->db->lastInsertId();

            // Insérer les détails
            if (!empty($data['besoins']) && !empty($data['quantites'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO s3_don_details (id_don, id_besoin, quantite)
                    VALUES (:id_don, :id_besoin, :quantite)
                ");

                foreach ($data['besoins'] as $index => $id_besoin) {
                    if (!empty($id_besoin) && !empty($data['quantites'][$index])) {
                        $stmt->execute([
                            ':id_don' => $id_don,
                            ':id_besoin' => $id_besoin,
                            ':quantite' => $data['quantites'][$index],
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateDon($id, $data)
    {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le don
            $stmt = $this->db->prepare("UPDATE s3_don SET date_don = :date_don WHERE id = :id");
            $stmt->execute([
                ':date_don' => $data['date_don'],
                ':id' => $id,
            ]);

            // Supprimer les anciens détails
            $stmt = $this->db->prepare("DELETE FROM s3_don_details WHERE id_don = :id_don");
            $stmt->execute([':id_don' => $id]);

            // Insérer les nouveaux détails
            if (!empty($data['besoins']) && !empty($data['quantites'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO s3_don_details (id_don, id_besoin, quantite)
                    VALUES (:id_don, :id_besoin, :quantite)
                ");

                foreach ($data['besoins'] as $index => $id_besoin) {
                    if (!empty($id_besoin) && !empty($data['quantites'][$index])) {
                        $stmt->execute([
                            ':id_don' => $id,
                            ':id_besoin' => $id_besoin,
                            ':quantite' => $data['quantites'][$index],
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteDon($id)
    {
        try {
            $this->db->beginTransaction();

            // Supprimer les détails
            $stmt = $this->db->prepare("DELETE FROM s3_don_details WHERE id_don = :id_don");
            $stmt->execute([':id_don' => $id]);

            // Supprimer le don
            $stmt = $this->db->prepare("DELETE FROM s3_don WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

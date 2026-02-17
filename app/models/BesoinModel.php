<?php

namespace app\models;

class BesoinModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllBesoins()
    {
        $stmt = $this->db->prepare("
            SELECT b.id, b.nom, b.prix, t.nom as type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            ORDER BY b.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function searchBesoins($search)
    {
        $searchTerm = '%' . $search . '%';
        $stmt = $this->db->prepare("
            SELECT b.id, b.nom, b.prix, t.nom as type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            WHERE b.nom LIKE :search OR t.nom LIKE :search
            ORDER BY b.id DESC
        ");
        $stmt->execute([':search' => $searchTerm]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBesoinById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM s3_besoin WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllTypes()
    {
        $stmt = $this->db->query("SELECT id, nom FROM s3_type_besoin ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllVilles()
    {
        $stmt = $this->db->query("SELECT id, nom FROM s3_ville ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBesoinsWithTypes()
    {
        $stmt = $this->db->prepare("
            SELECT b.id, b.nom, b.prix, t.nom as type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            ORDER BY b.nom
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function insertBesoin($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO s3_besoin (nom, id_type_besoin, prix)
            VALUES (:nom, :id_type_besoin, :prix)
        ");
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':id_type_besoin' => $data['id_type_besoin'],
            ':prix' => $data['prix'],
        ]);
    }

    public function updateBesoin($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE s3_besoin
            SET nom = :nom, id_type_besoin = :id_type_besoin, prix = :prix
            WHERE id = :id
        ");
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':id_type_besoin' => $data['id_type_besoin'],
            ':prix' => $data['prix'],
            ':id' => $id,
        ]);
    }

    public function deleteBesoin($id)
    {
        // Vérifier d'abord s'il y a des références dans s3_besoin_ville_details
        $checkStmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM s3_besoin_ville_details 
            WHERE id_besoin = :id
        ");
        $checkStmt->execute([':id' => $id]);
        $result = $checkStmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Ce besoin ne peut pas être supprimé car il est lié à une ou plusieurs villes.'
            ];
        }
        
        // Si aucune référence, procéder à la suppression
        $stmt = $this->db->prepare("DELETE FROM s3_besoin WHERE id = :id");
        $deleted = $stmt->execute([':id' => $id]);
        
        return [
            'success' => $deleted,
            'message' => $deleted ? 'Besoin supprimé avec succès.' : 'Erreur lors de la suppression du besoin.'
        ];
    }

    public function createBesoinVille($idVille, $dateBesoin, $besoins)
    {
        try {
            // Créer l'enregistrement besoin_ville
            $stmt = $this->db->prepare("
                INSERT INTO s3_besoin_ville (id_ville, date_besoin)
                VALUES (:id_ville, :date_besoin)
            ");
            $stmt->execute([
                ':id_ville' => $idVille,
                ':date_besoin' => $dateBesoin
            ]);

            $besoinVilleId = $this->db->lastInsertId();

            // Ajouter les détails des besoins
            $stmtDetails = $this->db->prepare("
                INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite)
                VALUES (:id_besoin_ville, :id_besoin, :quantite)
            ");

            foreach ($besoins as $besoin) {
                $stmtDetails->execute([
                    ':id_besoin_ville' => $besoinVilleId,
                    ':id_besoin' => $besoin['id_besoin'],
                    ':quantite' => $besoin['quantite']
                ]);
            }

            return $besoinVilleId;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

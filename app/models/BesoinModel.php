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
        $stmt = $this->db->prepare("DELETE FROM s3_besoin WHERE id = :id");
        return $stmt->execute([':id' => $id]);
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

    public function getOrCreateBesoin($nom, $idType, $prix)
    {
        // Vérifier si le besoin existe
        $stmt = $this->db->prepare("
            SELECT id FROM s3_besoin 
            WHERE nom = :nom AND id_type_besoin = :id_type
        ");
        $stmt->execute([
            ':nom' => $nom,
            ':id_type' => $idType
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['id'];
        }

        // Créer le besoin s'il n'existe pas
        $stmtInsert = $this->db->prepare("
            INSERT INTO s3_besoin (nom, id_type_besoin, prix)
            VALUES (:nom, :id_type, :prix)
        ");
        $stmtInsert->execute([
            ':nom' => $nom,
            ':id_type' => $idType,
            ':prix' => $prix
        ]);

        return $this->db->lastInsertId();
    }
}

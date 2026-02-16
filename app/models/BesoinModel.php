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
}

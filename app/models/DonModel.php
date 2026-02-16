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

    public function getAllTypesDon()
    {
        $stmt = $this->db->query("SELECT id, nom, description FROM s3_type_don ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllBesoins()
    {
        $stmt = $this->db->query("SELECT id, nom FROM s3_besoin ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBesoinsByType($type_don_nom)
    {
        // Mapper le type_don au type_besoin
        $type_mapping = [
            'nature' => 'nature',
            'matériel' => 'matériel',
            'financier' => 'argent'
        ];

        $type_besoin_nom = $type_mapping[$type_don_nom] ?? $type_don_nom;

        $stmt = $this->db->prepare("
            SELECT b.id, b.nom, b.prix, t.nom as type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            WHERE t.nom = :type_nom
            ORDER BY b.nom
        ");
        $stmt->execute([':type_nom' => $type_besoin_nom]);
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
            // Supporter deux formats : anciens formulaire (besoins[]) ou nouveau (types[])
            if (!empty($data['types']) && !empty($data['quantites'])) {
                // Mapping type_don -> type_besoin
                $type_mapping = [
                    'nature' => 'nature',
                    'matériel' => 'matériel',
                    'financier' => 'argent'
                ];

                $stmtGetType = $this->db->prepare("SELECT id FROM s3_type_besoin WHERE nom = :nom LIMIT 1");
                $stmtGetBesoin = $this->db->prepare("SELECT id FROM s3_besoin WHERE id_type_besoin = :id_type LIMIT 1");
                $stmtCreateBesoin = $this->db->prepare("INSERT INTO s3_besoin (id_type_besoin, nom, prix) VALUES (:id_type, :nom, :prix)");
                $stmtInsertDetail = $this->db->prepare("INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES (:id_don, :id_besoin, :quantite)");

                foreach ($data['types'] as $index => $type_nom) {
                    $quantite = $data['quantites'][$index] ?? 0;
                    if (empty($type_nom) || empty($quantite)) continue;

                    // cas spécial : l'utilisateur a choisi un besoin spécifique
                    if ($type_nom === 'besoin') {
                        $id_besoin_selected = $data['besoins'][$index] ?? null;
                        if (empty($id_besoin_selected)) continue;

                        $stmtInsertDetail->execute([
                            ':id_don' => $id_don,
                            ':id_besoin' => $id_besoin_selected,
                            ':quantite' => $quantite,
                        ]);
                        continue;
                    }

                    $type_besoin_nom = $type_mapping[$type_nom] ?? $type_nom;

                    // Récupérer id_type_besoin
                    $stmtGetType->execute([':nom' => $type_besoin_nom]);
                    $typeRow = $stmtGetType->fetch(\PDO::FETCH_ASSOC);
                    if (!$typeRow) {
                        // si le type de besoin n'existe pas, sauter
                        continue;
                    }
                    $id_type_besoin = $typeRow['id'];

                    // Récupérer un besoin existant pour ce type
                    $stmtGetBesoin->execute([':id_type' => $id_type_besoin]);
                    $besoinRow = $stmtGetBesoin->fetch(\PDO::FETCH_ASSOC);

                    if ($besoinRow) {
                        $id_besoin = $besoinRow['id'];
                    } else {
                        // Créer un besoin générique pour ce type
                        $nom_besoin = ucfirst($type_besoin_nom);
                        $stmtCreateBesoin->execute([
                            ':id_type' => $id_type_besoin,
                            ':nom' => $nom_besoin,
                            ':prix' => 0
                        ]);
                        $id_besoin = $this->db->lastInsertId();
                    }

                    // Insérer le détail
                    $stmtInsertDetail->execute([
                        ':id_don' => $id_don,
                        ':id_besoin' => $id_besoin,
                        ':quantite' => $quantite,
                    ]);
                }
            } elseif (!empty($data['besoins']) && !empty($data['quantites'])) {
                $stmt = $this->db->prepare("INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES (:id_don, :id_besoin, :quantite)");
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
}

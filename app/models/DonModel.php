<?php

namespace app\models;

class DonModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les dons (biens + financiers) triés par date
     */
    public function getAllDons()
    {
        $stmt = $this->db->query("
            SELECT 'besoin' AS source, d.id, d.date_don,
                   GROUP_CONCAT(CONCAT(b.nom, ' : ', dd.quantite) SEPARATOR ', ') AS details,
                   NULL AS montant
            FROM s3_don d
            LEFT JOIN s3_don_details dd ON d.id = dd.id_don
            LEFT JOIN s3_besoin b ON dd.id_besoin = b.id
            GROUP BY d.id, d.date_don

            UNION ALL

            SELECT 'financier' AS source, df.id, df.date_don,
                   NULL AS details,
                   df.montant
            FROM s3_don_financier df

            ORDER BY date_don DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recherche de dons avec filtres (texte + dates)
     */
    public function searchDons($search = '', $date_debut = '', $date_fin = '')
    {
        $besoinDons = $this->searchDonsBesoin($search, $date_debut, $date_fin);
        $finDons = $this->searchDonsFinancier($search, $date_debut, $date_fin);

        $all = array_merge($besoinDons, $finDons);
        usort($all, function ($a, $b) {
            return strtotime($b['date_don']) - strtotime($a['date_don']);
        });
        return $all;
    }

    /**
     * Recherche parmi les dons de biens
     */
    private function searchDonsBesoin($search, $date_debut, $date_fin)
    {
        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $conditions[] = "(d.date_don LIKE :search_a OR b.nom LIKE :search_b)";
            $params[':search_a'] = $searchTerm;
            $params[':search_b'] = $searchTerm;
        }
        if (!empty($date_debut)) {
            $conditions[] = "DATE(d.date_don) >= :date_debut";
            $params[':date_debut'] = $date_debut;
        }
        if (!empty($date_fin)) {
            $conditions[] = "DATE(d.date_don) <= :date_fin";
            $params[':date_fin'] = $date_fin;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->db->prepare("
            SELECT 'besoin' AS source, d.id, d.date_don,
                   GROUP_CONCAT(CONCAT(b.nom, ' : ', dd.quantite) SEPARATOR ', ') AS details,
                   NULL AS montant
            FROM s3_don d
            LEFT JOIN s3_don_details dd ON d.id = dd.id_don
            LEFT JOIN s3_besoin b ON dd.id_besoin = b.id
            {$where}
            GROUP BY d.id, d.date_don
            ORDER BY d.date_don DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recherche parmi les dons financiers
     */
    private function searchDonsFinancier($search, $date_debut, $date_fin)
    {
        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $conditions[] = "(df.date_don LIKE :search_a OR CAST(df.montant AS CHAR) LIKE :search_b)";
            $params[':search_a'] = $searchTerm;
            $params[':search_b'] = $searchTerm;
        }
        if (!empty($date_debut)) {
            $conditions[] = "DATE(df.date_don) >= :date_debut";
            $params[':date_debut'] = $date_debut;
        }
        if (!empty($date_fin)) {
            $conditions[] = "DATE(df.date_don) <= :date_fin";
            $params[':date_fin'] = $date_fin;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->db->prepare("
            SELECT 'financier' AS source, df.id, df.date_don,
                   NULL AS details,
                   df.montant
            FROM s3_don_financier df
            {$where}
            ORDER BY df.date_don DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les besoins (nature + matériel uniquement, pas d'argent)
     */
    public function getAllBesoins()
    {
        $stmt = $this->db->query("
            SELECT b.id, b.nom, tb.nom AS type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
            ORDER BY tb.nom, b.nom
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les besoins filtrés par type
     */
    public function getBesoinsByType($type_besoin_nom)
    {
        $stmt = $this->db->prepare("
            SELECT b.id, b.nom, b.prix, t.nom AS type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            WHERE t.nom = :type_nom
            ORDER BY b.nom
        ");
        $stmt->execute([':type_nom' => $type_besoin_nom]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Insère un don — financier ou de biens selon type_don
     */
    public function insertDon($data)
    {
        $typeDon = $data['type_don'] ?? '';

        if ($typeDon === 'financier') {
            return $this->insertDonFinancier($data);
        }

        return $this->insertDonBesoin($data);
    }

    /**
     * Insère un don financier dans s3_don_financier
     */
    private function insertDonFinancier($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO s3_don_financier (montant, date_don) VALUES (:montant, :date_don)
        ");
        return $stmt->execute([
            ':montant' => (int) $data['montant'],
            ':date_don' => $data['date_don'],
        ]);
    }

    /**
     * Insère un don de biens dans s3_don + s3_don_details
     */
    private function insertDonBesoin($data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO s3_don (date_don) VALUES (:date_don)");
            $stmt->execute([':date_don' => $data['date_don']]);
            $id_don = $this->db->lastInsertId();

            if (!empty($data['besoins']) && !empty($data['quantites'])) {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO s3_don_details (id_don, id_besoin, quantite) 
                    VALUES (:id_don, :id_besoin, :quantite)
                ");
                foreach ($data['besoins'] as $index => $id_besoin) {
                    $quantite = $data['quantites'][$index] ?? 0;
                    if (!empty($id_besoin) && !empty($quantite)) {
                        $stmtDetail->execute([
                            ':id_don' => $id_don,
                            ':id_besoin' => $id_besoin,
                            ':quantite' => (int) $quantite,
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

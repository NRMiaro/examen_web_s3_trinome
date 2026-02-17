<?php

namespace app\models;

use Flight;

class AchatModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les achats avec filtrage optionnel par ville
     */
    public function getAllAchats($id_ville = '')
    {
        $conditions = [];
        $params = [];

        if (!empty($id_ville)) {
            $conditions[] = "a.id_ville = :id_ville";
            $params[':id_ville'] = $id_ville;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->db->prepare("
            SELECT a.id, a.quantite, a.prix_unitaire, a.frais_pourcent, a.total, a.date_achat,
                   b.nom AS besoin_nom, v.nom AS ville_nom
            FROM s3_achat a
            JOIN s3_besoin b ON a.id_besoin = b.id
            JOIN s3_ville v ON a.id_ville = v.id
            {$whereClause}
            ORDER BY a.date_achat DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le solde de la caisse (dons argent - achats)
     */
    public function getSoldeCaisse()
    {
        $stmt = $this->db->query("SELECT * FROM v_solde_caisse");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les besoins restants (manquants) par ville depuis les données VALIDÉES.
     * Formule : Manquant = Demandé - Alloué(validé) - Acheté
     * Ne retourne que les lignes où Manquant > 0.
     */
    public function getBesoinsRestants()
    {
        $sql = "
            SELECT 
                bvd.id_besoin_ville,
                bvd.id_besoin,
                b.nom AS besoin_nom,
                b.prix AS prix_unitaire,
                tb.nom AS type_nom,
                v.nom AS ville_nom,
                v.id AS id_ville,
                bvd.quantite AS quantite_demandee,
                COALESCE(dv.quantite_allouee, 0) AS alloue,
                COALESCE(achats.total_achete, 0) AS deja_achete,
                (bvd.quantite - COALESCE(dv.quantite_allouee, 0) - COALESCE(achats.total_achete, 0)) AS manquant
            FROM s3_besoin_ville_details bvd
            JOIN s3_besoin_ville bv ON bvd.id_besoin_ville = bv.id
            JOIN s3_ville v ON bv.id_ville = v.id
            JOIN s3_besoin b ON bvd.id_besoin = b.id
            JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
            LEFT JOIN s3_dispatch_validation dv 
                ON dv.id_besoin_ville = bvd.id_besoin_ville 
                AND dv.id_besoin = bvd.id_besoin
            LEFT JOIN (
                SELECT id_besoin, id_ville, SUM(quantite) AS total_achete
                FROM s3_achat
                GROUP BY id_besoin, id_ville
            ) achats 
                ON achats.id_besoin = bvd.id_besoin 
                AND achats.id_ville = bv.id_ville
            HAVING manquant > 0
            ORDER BY b.nom, bv.date_besoin, v.nom
        ";
        
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $besoinsRestants = [];
        foreach ($rows as $row) {
            $besoinsRestants[] = [
                'id_besoin'         => (int) $row['id_besoin'],
                'id_besoin_ville'   => (int) $row['id_besoin_ville'],
                'id_ville'          => (int) $row['id_ville'],
                'besoin_nom'        => $row['besoin_nom'],
                'ville_nom'         => $row['ville_nom'],
                'quantite_demandee' => (int) $row['quantite_demandee'],
                'alloue'            => (int) $row['alloue'],
                'deja_achete'       => (int) $row['deja_achete'],
                'manquant'          => (int) $row['manquant'],
                'prix_unitaire'     => (int) $row['prix_unitaire'],
                'type_nom'          => $row['type_nom'],
            ];
        }
        
        return $besoinsRestants;
    }

    /**
     * Vérifie si un besoin est encore couvert par les dons restants après validation.
     * Si les dons non-alloués (après validation) suffisent, pas besoin d'acheter.
     */
    public function besoinDejaDisponible($id_besoin, $quantite)
    {
        // Dons bruts par besoin
        $dons = \app\models\DashboardModel::getDonsObtenus();
        
        // Quantités déjà validées (allouées) par besoin
        $stmtValidees = $this->db->query("
            SELECT b.nom AS besoin_nom, SUM(dv.quantite_allouee) AS total_alloue
            FROM s3_dispatch_validation dv
            JOIN s3_besoin b ON dv.id_besoin = b.id
            GROUP BY b.id, b.nom
        ");
        $validees = [];
        while ($row = $stmtValidees->fetch(\PDO::FETCH_ASSOC)) {
            $validees[$row['besoin_nom']] = (int) $row['total_alloue'];
        }

        // Dons restants = dons bruts - validés
        $donsRestants = [];
        foreach ($dons as $nom => $qte) {
            $donsRestants[$nom] = $qte - ($validees[$nom] ?? 0);
            if ($donsRestants[$nom] < 0) {
                $donsRestants[$nom] = 0;
            }
        }

        // Trouver le nom du besoin
        $stmt = $this->db->prepare("SELECT nom FROM s3_besoin WHERE id = :id");
        $stmt->execute([':id' => $id_besoin]);
        $besoin = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($besoin && isset($donsRestants[$besoin['nom']]) && $donsRestants[$besoin['nom']] >= $quantite) {
            return true;
        }
        
        return false;
    }

    /**
     * Récupère toutes les villes
     */
    public function getAllVilles()
    {
        $stmt = $this->db->query("SELECT id, nom FROM s3_ville ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Insère un nouvel achat
     */
    public function insertAchat($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO s3_achat (id_besoin, id_ville, quantite, prix_unitaire, frais_pourcent, total, date_achat)
            VALUES (:id_besoin, :id_ville, :quantite, :prix_unitaire, :frais_pourcent, :total, :date_achat)
        ");
        return $stmt->execute([
            ':id_besoin' => $data['id_besoin'],
            ':id_ville' => $data['id_ville'],
            ':quantite' => $data['quantite'],
            ':prix_unitaire' => $data['prix_unitaire'],
            ':frais_pourcent' => $data['frais_pourcent'],
            ':total' => $data['total'],
            ':date_achat' => $data['date_achat'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Supprime un achat
     */
    public function deleteAchat($id)
    {
        $stmt = $this->db->prepare("DELETE FROM s3_achat WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

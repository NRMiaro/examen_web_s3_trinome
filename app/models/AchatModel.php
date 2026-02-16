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
     * Récupère les besoins restants (manquants) par ville depuis le dispatch
     * Ce sont les besoins en nature/matériel non couverts par les dons
     */
    public function getBesoinsRestants()
    {
        // Utiliser le DispatchModel pour obtenir les besoins non résolus
        $dispatch_data = \app\models\DispatchModel::getDispatchComplet();
        
        $besoinsRestants = [];
        foreach ($dispatch_data['dispatch'] as $cle => $item) {
            if ($item['manquant'] > 0) {
                // Récupérer le prix et type du besoin
                $stmtType = $this->db->prepare("
                    SELECT tb.nom AS type_nom, b.prix 
                    FROM s3_besoin b 
                    JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id 
                    WHERE b.id = :id
                ");
                $stmtType->execute([':id' => $item['id_besoin']]);
                $typeInfo = $stmtType->fetch(\PDO::FETCH_ASSOC);
                
                if ($typeInfo) {
                    $besoinsRestants[] = [
                        'id_besoin' => $item['id_besoin'],
                        'id_besoin_ville' => $item['id_besoin_ville'],
                        'besoin_nom' => $item['besoin_nom'],
                        'ville_nom' => $item['ville_nom'],
                        'quantite_demandee' => $item['quantite_demandee'],
                        'alloue' => $item['alloue'],
                        'manquant' => $item['manquant'],
                        'prix_unitaire' => (int) $typeInfo['prix'],
                        'type_nom' => $typeInfo['type_nom'],
                    ];
                }
            }
        }
        
        return $besoinsRestants;
    }

    /**
     * Vérifie si un besoin est déjà couvert par les dons restants (pas d'achat nécessaire)
     */
    public function besoinDejaDisponible($id_besoin, $quantite)
    {
        $dons = \app\models\DashboardModel::getDonsObtenus();
        $dispatch_data = \app\models\DispatchModel::getDispatchComplet();
        
        // Calculer les dons restants (non alloués)
        $donsRestants = $dons;
        foreach ($dispatch_data['dispatch'] as $item) {
            $besoinNom = $item['besoin_nom'];
            if (isset($donsRestants[$besoinNom])) {
                $donsRestants[$besoinNom] -= $item['alloue'];
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

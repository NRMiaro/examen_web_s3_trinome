<?php

namespace app\models;

use Flight;

/**
 * Model pour le tableau de bord
 * Récupère les données agrégées des besoins et dons
 */
class DashboardModel
{
    /**
     * Récupère la liste de toutes les villes
     * @return array ['id' => nom, ...]
     */
    public static function getListeVilles(): array
    {
        $sql = "SELECT id, nom FROM s3_ville ORDER BY nom";
        $statement = Flight::db()->query($sql);
        $result = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row['id']] = $row['nom'];
        }
        return $result;
    }

    public static function getDonsObtenus(): array
    {
        $sql = "SELECT * FROM v_qte_dons_obtenus";
        $statement = Flight::db()->query($sql);
        $result = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $nom_besoin = $row['nom_besoin'];
            $result[$nom_besoin] = (int) $row['quantite'];
        }
        return $result;
    }

    /**
     * Récupère les besoins par ville avec les détails
     * Hiérarchie : Ville > Besoin-Ville (demande) > Produits (besoins)
     * @return array Liste des villes avec leurs demandes et produits
     */
    public static function getBesoinsParVille(): array
    {
        $sql = "
            SELECT 
                bv.id as besoin_ville_id,
                v.id as ville_id,
                v.nom as ville_nom,
                DATE_FORMAT(bv.date_besoin, '%d/%m/%Y') as date_besoin,
                b.nom as besoin_nom,
                b.prix as besoin_prix,
                bvd.quantite,
                tb.nom as type_besoin
            FROM s3_besoin_ville bv
            JOIN s3_ville v 
                ON bv.id_ville = v.id
            JOIN s3_besoin_ville_details bvd 
                ON bvd.id_besoin_ville = bv.id
            JOIN s3_besoin b 
                ON bvd.id_besoin = b.id
            JOIN s3_type_besoin tb 
                ON b.id_type_besoin = tb.id
            ORDER BY v.nom, bv.id, b.nom
        ";
        $statement = Flight::db()->query($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // Regrouper par ville > besoin_ville > produits
        $villes = [];
        foreach ($rows as $row) {
            $villeId = $row['ville_id'];
            $besoinVilleId = $row['besoin_ville_id'];
            
            // Créer la ville si elle n'existe pas
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'id' => $villeId,
                    'nom' => $row['ville_nom'],
                    'demandes' => []
                ];
            }
            
            // Créer la demande (besoin_ville) si elle n'existe pas
            if (!isset($villes[$villeId]['demandes'][$besoinVilleId])) {
                $villes[$villeId]['demandes'][$besoinVilleId] = [
                    'id_besoin_ville' => $besoinVilleId,
                    'date' => $row['date_besoin'],
                    'produits' => []
                ];
            }
            
            // Ajouter le produit à la demande
            $villes[$villeId]['demandes'][$besoinVilleId]['produits'][] = [
                'nom' => $row['besoin_nom'],
                'quantite' => (int) $row['quantite'],
                'prix' => (int) $row['besoin_prix'],
                'type' => $row['type_besoin'],
                'unite' => self::getUnite($row['besoin_nom'])
            ];
        }
        
        // Convertir en arrays et réindexer les demandes
        foreach ($villes as &$ville) {
            $ville['demandes'] = array_values($ville['demandes']);
        }
        
        return array_values($villes);
    }

    public static function getTotalDemandeParBesoin(): array
    {
        $sql = "SELECT * FROM v_qte_besoins_villes";
        $statement = Flight::db()->query($sql);
        $result = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $nom = $row['nom'];
            $result[$nom] = (int) $row['total'];
        }
        return $result;
    }

    /**
     * Retourne l'unité de mesure selon le type de besoin
     * @param string $nomBesoin
     * @return string
     */
    private static function getUnite(string $nomBesoin): string
    {
        $unites = [
            'Riz' => 'kg',
            'Huile' => 'L',
            'Eau' => 'L',
            'Couvertures' => 'pcs',
            'Nourriture' => 'portions'
        ];
        return $unites[$nomBesoin] ?? 'unités';
    }
}

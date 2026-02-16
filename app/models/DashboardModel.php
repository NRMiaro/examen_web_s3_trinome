<?php

namespace app\models;

use Flight;

/**
 * Model pour le tableau de bord
 * Récupère les données agrégées des besoins et dons
 */
class DashboardModel
{
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
     * @return array Liste des villes avec leurs besoins
     */
    public static function getBesoinsParVille(): array
    {
        $sql = "
            SELECT 
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
            ORDER BY v.nom, b.nom
        ";
        $statement = Flight::db()->query($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // Regrouper par ville
        $villes = [];
        foreach ($rows as $row) {
            $villeId = $row['ville_id'];
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'nom' => $row['ville_nom'],
                    'date' => $row['date_besoin'],
                    'besoins' => []
                ];
            }
            $villes[$villeId]['besoins'][] = [
                'nom' => $row['besoin_nom'],
                'quantite' => (int) $row['quantite'],
                'prix' => (int) $row['besoin_prix'],
                'type' => $row['type_besoin'],
                'unite' => self::getUnite($row['besoin_nom'])
            ];
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

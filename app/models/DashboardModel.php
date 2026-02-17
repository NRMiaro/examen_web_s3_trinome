<?php

namespace app\models;

use Flight;


class DashboardModel
{
   
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

 
    public static function getBesoinsParVille(): array
    {
        $sql = "
            SELECT 
                bv.id as besoin_ville_id,
                v.id as ville_id,
                v.nom as ville_nom,
                DATE_FORMAT(bv.date_besoin, '%d/%m/%Y') as date_besoin,
                b.id as id_besoin,
                b.nom as besoin_nom,
                b.prix as besoin_prix,
                bvd.quantite,
                bvd.ordre,
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
            ORDER BY v.nom, bvd.ordre, bv.id, b.nom
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
                'id_besoin' => (int) $row['id_besoin'],
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

    private static function getUnite(string $nomBesoin): string
    {
        $unites = [
            'Riz' => 'kg',
            'Riz (kg)' => 'kg',
            'Huile' => 'L',
            'Huile (L)' => 'L',
            'Eau' => 'L',
            'Eau (L)' => 'L',
            'Haricots' => 'kg',
            'Couvertures' => 'pcs',
            'Nourriture' => 'portions',
            'Tôle' => 'pcs',
            'Bâche' => 'pcs',
            'Clous (kg)' => 'kg',
            'Bois' => 'pcs',
            'groupe' => 'pcs',
            'Argent' => 'Ar',
        ];
        return $unites[$nomBesoin] ?? 'unités';
    }

    public static function getDispatchValide(): array
    {
        $sql = "
            SELECT 
                bvd.id_besoin_ville,
                bvd.id_besoin,
                b.nom AS besoin_nom,
                v.nom AS ville_nom,
                bv.date_besoin,
                bvd.quantite AS quantite_demandee,
                COALESCE(dv.quantite_allouee, 0) AS alloue,
                COALESCE(achats.total_achete, 0) AS achete,
                GREATEST(0, CAST(bvd.quantite AS SIGNED) - COALESCE(dv.quantite_allouee, 0) - COALESCE(achats.total_achete, 0)) AS manquant,
                CASE 
                    WHEN (COALESCE(dv.quantite_allouee, 0) + COALESCE(achats.total_achete, 0)) >= bvd.quantite THEN 'resolved'
                    WHEN (COALESCE(dv.quantite_allouee, 0) + COALESCE(achats.total_achete, 0)) > 0 THEN 'partial'
                    ELSE 'unresolved'
                END AS statut,
                CASE WHEN bvd.quantite > 0 
                    THEN LEAST(100, ROUND(((COALESCE(dv.quantite_allouee, 0) + COALESCE(achats.total_achete, 0)) / bvd.quantite) * 100)) 
                    ELSE 0 
                END AS pourcentage
            FROM s3_besoin_ville_details bvd
            JOIN s3_besoin_ville bv ON bvd.id_besoin_ville = bv.id
            JOIN s3_ville v ON bv.id_ville = v.id
            JOIN s3_besoin b ON bvd.id_besoin = b.id
            LEFT JOIN s3_dispatch_validation dv 
                ON dv.id_besoin_ville = bvd.id_besoin_ville AND dv.id_besoin = bvd.id_besoin
            LEFT JOIN (
                SELECT id_besoin, id_ville, SUM(quantite) AS total_achete
                FROM s3_achat
                GROUP BY id_besoin, id_ville
            ) achats ON achats.id_besoin = bvd.id_besoin AND achats.id_ville = bv.id_ville
            ORDER BY b.nom, bv.date_besoin, v.nom
        ";
        error_log("getDispatchValide: ENTERING");
        $statement = Flight::db()->query($sql);
        error_log("getDispatchValide: statement type=" . get_class($statement));
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        error_log("getDispatchValide: rows=" . count($rows));
        if (empty($rows)) {
            $st2 = Flight::db()->query("SELECT COUNT(*) as c FROM s3_besoin_ville_details");
            $c = $st2->fetch(\PDO::FETCH_ASSOC);
            error_log("getDispatchValide: bvd count=" . $c['c']);
        }

        $index = [];
        foreach ($rows as $row) {
            $cle = $row['id_besoin_ville'] . '_' . $row['id_besoin'];
            $index[$cle] = $row;
        }
        return $index;
    }

    public static function getDonsMontantsValidees(): array
    {
        $sql = "
            SELECT b.nom AS besoin_nom, SUM(dv.quantite_allouee) AS total_alloue
            FROM s3_dispatch_validation dv
            JOIN s3_besoin b ON dv.id_besoin = b.id
            GROUP BY b.id, b.nom
        ";
        $statement = Flight::db()->query($sql);
        $result = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row['besoin_nom']] = (int) $row['total_alloue'];
        }
        return $result;
    }
}

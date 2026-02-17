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
            'Huile' => 'L',
            'Eau' => 'L',
            'Couvertures' => 'pcs',
            'Nourriture' => 'portions'
        ];
        return $unites[$nomBesoin] ?? 'unités';
    }

    /**
     * Récupère l'état complet de toutes les demandes (coverage).
     * Part de s3_besoin_ville_details pour toujours avoir des lignes,
     * même quand aucune validation/achat n'a été faite.
     * @return array ['id_besoin_ville_id_besoin' => row, ...]
     */
    public static function getDispatchValide(): array
    {
        $sql = "
            SELECT 
                sdv.id_besoin_ville,
                sdv.id_besoin,
                sdv.quantite_demandee,
                sdv.quantite_allouee,
                sdv.quantite_manquante,
                sdv.statut,
                bv.date_besoin,
                bv.id_ville,
                v.nom AS ville_nom,
                b.nom AS besoin_nom
            FROM s3_dispatch_validation sdv
            JOIN s3_besoin_ville bv ON sdv.id_besoin_ville = bv.id
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
        
        // Récupérer les achats par ville + besoin
        $achats = self::getAchatsParVilleBesoin();
        
        $dispatch = [];
        
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $cle = $row['id_besoin_ville'] . '_' . $row['id_besoin'];
            
            // Quantité allouée par le dispatch validé
            $alloueDispatch = (int) $row['quantite_allouee'];
            
            // Quantité achetée pour cette ville + ce besoin
            $cleAchat = $row['id_ville'] . '_' . $row['id_besoin'];
            $achete = $achats[$cleAchat] ?? 0;
            
            // Total réellement couvert = dispatch + achats
            $totalCouvert = $alloueDispatch + $achete;
            $quantiteDemandee = (int) $row['quantite_demandee'];
            $manquant = max(0, $quantiteDemandee - $totalCouvert);
            
            // Recalculer le statut en tenant compte des achats
            if ($manquant === 0) {
                $statut = 'resolved';
            } elseif ($totalCouvert > 0) {
                $statut = 'partial';
            } else {
                $statut = 'unresolved';
            }
            
            $pourcentage = $quantiteDemandee > 0 
                ? min(100, round(($totalCouvert / $quantiteDemandee) * 100))
                : 0;
            
            $dispatch[$cle] = [
                'id_besoin_ville' => $row['id_besoin_ville'],
                'id_besoin' => $row['id_besoin'],
                'besoin_nom' => $row['besoin_nom'],
                'ville_nom' => $row['ville_nom'],
                'date_besoin' => $row['date_besoin'],
                'quantite_demandee' => $quantiteDemandee,
                'alloue' => $totalCouvert,
                'alloue_dispatch' => $alloueDispatch,
                'alloue_achat' => $achete,
                'manquant' => $manquant,
                'statut' => $statut,
                'pourcentage' => $pourcentage
            ];
        }
        
        return $dispatch;
    }

    /**
     * Récupère les quantités achetées groupées par ville + besoin
     * @return array ['id_ville_id_besoin' => quantite, ...]
     */
    public static function getAchatsParVilleBesoin(): array
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

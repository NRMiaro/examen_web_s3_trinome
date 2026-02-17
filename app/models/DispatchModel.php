<?php

namespace app\models;

use Flight;

class DispatchModel
{
    public static function getDemandesTotales()
    {
        $sql = "
            SELECT 
                bv.id AS id_besoin_ville,
                bv.date_besoin,
                v.id AS id_ville,
                v.nom AS ville_nom,
                b.id AS id_besoin,
                b.nom AS besoin_nom,
                bvd.quantite AS quantite_demandee
            FROM s3_besoin_ville bv
            JOIN s3_ville v ON bv.id_ville = v.id
            JOIN s3_besoin_ville_details bvd ON bvd.id_besoin_ville = bv.id
            JOIN s3_besoin b ON bvd.id_besoin = b.id
            ORDER BY b.nom, bv.date_besoin, v.nom
        ";
        $statement = Flight::db()->query($sql);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getDemandesTotalesNonValidees()
    {
        $sql = "
            SELECT 
                bv.id AS id_besoin_ville,
                bv.date_besoin,
                v.id AS id_ville,
                v.nom AS ville_nom,
                b.id AS id_besoin,
                b.nom AS besoin_nom,
                bvd.quantite AS quantite_demandee
            FROM s3_besoin_ville bv
            JOIN s3_ville v ON bv.id_ville = v.id
            JOIN s3_besoin_ville_details bvd ON bvd.id_besoin_ville = bv.id
            JOIN s3_besoin b ON bvd.id_besoin = b.id
            WHERE NOT EXISTS (
                SELECT 1 FROM s3_dispatch_validation sdv
                WHERE sdv.id_besoin_ville = bv.id
                AND sdv.id_besoin = b.id
            )
            ORDER BY b.nom, bv.date_besoin, v.nom
        ";
        $statement = Flight::db()->query($sql);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function getDemandesAvecManque()
    {
        $sql = "
            SELECT 
                bv.id AS id_besoin_ville,
                bv.date_besoin,
                v.id AS id_ville,
                v.nom AS ville_nom,
                b.id AS id_besoin,
                b.nom AS besoin_nom,
                GREATEST(0,
                    CASE 
                        WHEN sdv.id IS NOT NULL THEN sdv.quantite_manquante
                        ELSE bvd.quantite
                    END
                    - COALESCE(achats.total_achete, 0)
                ) AS quantite_demandee
            FROM s3_besoin_ville bv
            JOIN s3_ville v ON bv.id_ville = v.id
            JOIN s3_besoin_ville_details bvd ON bvd.id_besoin_ville = bv.id
            JOIN s3_besoin b ON bvd.id_besoin = b.id
            LEFT JOIN s3_dispatch_validation sdv 
                ON sdv.id_besoin_ville = bv.id AND sdv.id_besoin = b.id
            LEFT JOIN (
                SELECT id_besoin, id_ville, SUM(quantite) AS total_achete
                FROM s3_achat
                GROUP BY id_besoin, id_ville
            ) achats ON achats.id_besoin = b.id AND achats.id_ville = bv.id_ville
            WHERE sdv.id IS NULL 
               OR sdv.quantite_manquante > 0
            HAVING quantite_demandee > 0
            ORDER BY b.nom, bv.date_besoin, v.nom
        ";
        $statement = Flight::db()->query($sql);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function calculerDispatch($dons, $demandes)
    {
        // Grouper les demandes par besoin (type de produit) et trier par date
        $demandesParBesoin = [];
        foreach ($demandes as $demande) {
            $besoinNom = $demande['besoin_nom'];
            if (!isset($demandesParBesoin[$besoinNom])) {
                $demandesParBesoin[$besoinNom] = [];
            }
            $demandesParBesoin[$besoinNom][] = $demande;
        }

        // Calculer le dispatch pour chaque besoin
        $dispatch = [];
        $resteParBesoin = $dons; // Copie du stock de dons

        foreach ($demandesParBesoin as $besoinNom => $demandesList) {
            $resteActuel = $resteParBesoin[$besoinNom] ?? 0;

            foreach ($demandesList as $demande) {
                $idBesoinVille = $demande['id_besoin_ville'];
                $idBesoin = $demande['id_besoin'];
                $quantiteDemandee = (int) $demande['quantite_demandee'];

                // Calculer l'allocation
                $alloue = min($quantiteDemandee, $resteActuel);
                $manquant = $quantiteDemandee - $alloue;
                $resteActuel -= $alloue;

                // Déterminer le statut
                if ($manquant === 0) {
                    $statut = 'resolved'; 
                } elseif ($alloue > 0) {
                    $statut = 'partial'; 
                } else {
                    $statut = 'unresolved'; 
                }

                // Clé unique : combinaison id_besoin_ville + id_besoin
                $cle = $idBesoinVille . '_' . $idBesoin;

                $dispatch[$cle] = [
                    'id_besoin_ville' => $idBesoinVille,
                    'id_besoin' => $idBesoin,
                    'besoin_nom' => $besoinNom,
                    'ville_nom' => $demande['ville_nom'],
                    'date_besoin' => $demande['date_besoin'],
                    'quantite_demandee' => $quantiteDemandee,
                    'alloue' => $alloue,
                    'manquant' => $manquant,
                    'statut' => $statut,
                    'pourcentage' => $quantiteDemandee > 0 ? round(($alloue / $quantiteDemandee) * 100) : 0
                ];
            }

            // Mettre à jour le reste pour ce besoin
            $resteParBesoin[$besoinNom] = $resteActuel;
        }

        return $dispatch;
    }

    public static function getQuantitesDejaValidees(): array
    {
        $sql = "
            SELECT b.nom AS besoin_nom, SUM(sdv.quantite_allouee) AS total_alloue
            FROM s3_dispatch_validation sdv
            JOIN s3_besoin b ON sdv.id_besoin = b.id
            GROUP BY b.id, b.nom
        ";
        $statement = Flight::db()->query($sql);
        $result = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row['besoin_nom']] = (int) $row['total_alloue'];
        }
        return $result;
    }


    public static function getDispatchComplet($onlyNonValidated = false)
    {
        $donsModel = new \app\models\DashboardModel();
        $dons = $donsModel::getDonsObtenus(); // Total brut des dons matériels
        
        if ($onlyNonValidated) {
            // Soustraire les quantités déjà validées du stock disponible
            $dejaValide = self::getQuantitesDejaValidees();
            foreach ($dejaValide as $besoinNom => $quantiteValidee) {
                if (isset($dons[$besoinNom])) {
                    $dons[$besoinNom] -= $quantiteValidee;
                    if ($dons[$besoinNom] < 0) {
                        $dons[$besoinNom] = 0;
                    }
                }
            }
            
            // Récupérer les demandes avec manque restant
            // (non validées + validées partiellement/non résolues)
            $demandes = self::getDemandesAvecManque();
        } else {
            $demandes = self::getDemandesTotales();
        }
        
        $dispatch = self::calculerDispatch($dons, $demandes);

        return [
            'dons' => $dons,
            'demandes' => $demandes,
            'dispatch' => $dispatch
        ];
    }
}

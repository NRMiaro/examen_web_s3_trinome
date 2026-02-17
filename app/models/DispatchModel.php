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

    public const STRATEGIE_DATE = 'date';
    public const STRATEGIE_QUANTITE = 'quantite';
    public const STRATEGIE_EQUITABLE = 'equitable';

    public static function getStrategiesDisponibles(): array
    {
        return [
            self::STRATEGIE_DATE => [
                'nom' => 'Par date',
                'description' => 'Priorité aux demandes les plus anciennes',
                'icon' => 'bi-calendar-check',
            ],
            self::STRATEGIE_QUANTITE => [
                'nom' => 'Par quantité',
                'description' => 'Priorité aux plus petites demandes',
                'icon' => 'bi-sort-numeric-down',
            ],
            self::STRATEGIE_EQUITABLE => [
                'nom' => 'Équitable',
                'description' => 'Répartition égale entre les villes',
                'icon' => 'bi-pie-chart',
            ],
        ];
    }

    private static function grouperParBesoin(array $demandes): array
    {
        $demandesParBesoin = [];
        foreach ($demandes as $demande) {
            $besoinNom = $demande['besoin_nom'];
            if (!isset($demandesParBesoin[$besoinNom])) {
                $demandesParBesoin[$besoinNom] = [];
            }
            $demandesParBesoin[$besoinNom][] = $demande;
        }
        return $demandesParBesoin;
    }


    private static function buildDispatchItem(array $demande, int $alloue): array
    {
        $quantiteDemandee = (int) $demande['quantite_demandee'];
        $manquant = $quantiteDemandee - $alloue;

        if ($manquant === 0) {
            $statut = 'resolved';
        } elseif ($alloue > 0) {
            $statut = 'partial';
        } else {
            $statut = 'unresolved';
        }

        return [
            'id_besoin_ville' => $demande['id_besoin_ville'],
            'id_besoin' => $demande['id_besoin'],
            'besoin_nom' => $demande['besoin_nom'],
            'ville_nom' => $demande['ville_nom'],
            'date_besoin' => $demande['date_besoin'],
            'quantite_demandee' => $quantiteDemandee,
            'alloue' => $alloue,
            'manquant' => $manquant,
            'statut' => $statut,
            'pourcentage' => $quantiteDemandee > 0 ? round(($alloue / $quantiteDemandee) * 100) : 0
        ];
    }

    public static function calculerDispatch($dons, $demandes)
    {
        $demandesParBesoin = self::grouperParBesoin($demandes);
        $dispatch = [];
        $resteParBesoin = $dons;

        foreach ($demandesParBesoin as $besoinNom => $demandesList) {
            // Déjà trié par date dans la requête SQL
            $resteActuel = $resteParBesoin[$besoinNom] ?? 0;

            foreach ($demandesList as $demande) {
                $quantiteDemandee = (int) $demande['quantite_demandee'];
                $alloue = min($quantiteDemandee, $resteActuel);
                $resteActuel -= $alloue;

                $cle = $demande['id_besoin_ville'] . '_' . $demande['id_besoin'];
                $dispatch[$cle] = self::buildDispatchItem($demande, $alloue);
            }

            $resteParBesoin[$besoinNom] = $resteActuel;
        }
        
        return $dispatch;
    }

    public static function calculerDispatchParQuantite($dons, $demandes)
    {
        $demandesParBesoin = self::grouperParBesoin($demandes);
        $dispatch = [];
        $resteParBesoin = $dons;

        foreach ($demandesParBesoin as $besoinNom => $demandesList) {
            // Trier par quantité demandée croissante
            usort($demandesList, function ($a, $b) {
                return (int) $a['quantite_demandee'] <=> (int) $b['quantite_demandee'];
            });

            $resteActuel = $resteParBesoin[$besoinNom] ?? 0;

            foreach ($demandesList as $demande) {
                $quantiteDemandee = (int) $demande['quantite_demandee'];
                $alloue = min($quantiteDemandee, $resteActuel);
                $resteActuel -= $alloue;

                $cle = $demande['id_besoin_ville'] . '_' . $demande['id_besoin'];
                $dispatch[$cle] = self::buildDispatchItem($demande, $alloue);
            }

            $resteParBesoin[$besoinNom] = $resteActuel;
        }

        return $dispatch;
    }

    public static function calculerDispatchEquitable($dons, $demandes)
    {
        $demandesParBesoin = self::grouperParBesoin($demandes);
        $dispatch = [];
        $resteParBesoin = $dons;

        foreach ($demandesParBesoin as $besoinNom => $demandesList) {
            $stock = $resteParBesoin[$besoinNom] ?? 0;

            // Calculer la somme totale des demandes pour ce produit
            $sommeDemandes = 0;
            foreach ($demandesList as $demande) {
                $sommeDemandes += (int) $demande['quantite_demandee'];
            }

            // Initialiser les allocations
            $allocations = [];
            foreach ($demandesList as $i => $demande) {
                $allocations[$i] = 0;
            }

            if ($stock > 0 && $sommeDemandes > 0) {
                // Si le stock couvre toute la demande, donner exactement ce qui est demandé
                if ($stock >= $sommeDemandes) {
                    foreach ($demandesList as $i => $demande) {
                        $allocations[$i] = (int) $demande['quantite_demandee'];
                    }
                } else {
                    // Calculer la part proportionnelle exacte de chaque ville
                    $ratio = $stock / $sommeDemandes;
                    $partsExactes = [];
                    foreach ($demandesList as $i => $demande) {
                        $qte = (int) $demande['quantite_demandee'];
                        $partExacte = $ratio * $qte;
                        $partsExactes[$i] = $partExacte;
                    }

                    // Étape 1 : Arrondir vers le bas (floor)
                    $partiesDecimales = [];
                    foreach ($partsExactes as $i => $partExacte) {
                        $qte = (int) $demandesList[$i]['quantite_demandee'];
                        $arrondi = (int) floor($partExacte);
                        // Plafonner à la demande réelle
                        $allocations[$i] = min($arrondi, $qte);
                        // Garder la partie décimale pour le tri
                        $partiesDecimales[$i] = $partExacte - floor($partExacte);
                    }

                    // Étape 2 : Calculer le reste à distribuer
                    $sommeArrondie = array_sum($allocations);
                    $reste = $stock - $sommeArrondie;

                    // Étape 3 : Trier par partie décimale décroissante
                    arsort($partiesDecimales);

                    // Distribuer le reste (+1) aux indices avec les plus grands décimaux
                    foreach ($partiesDecimales as $i => $decimal) {
                        if ($reste <= 0) {
                            break;
                        }
                        $qte = (int) $demandesList[$i]['quantite_demandee'];
                        // Ne pas dépasser la demande réelle
                        if ($allocations[$i] < $qte) {
                            $allocations[$i] += 1;
                            $reste -= 1;
                        }
                    }
                }
            }

            // Construire les lignes de dispatch
            foreach ($demandesList as $i => $demande) {
                $cle = $demande['id_besoin_ville'] . '_' . $demande['id_besoin'];
                $dispatch[$cle] = self::buildDispatchItem($demande, $allocations[$i]);
            }

            $totalAlloue = array_sum($allocations);
            $resteParBesoin[$besoinNom] = $stock - $totalAlloue;
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


    public static function getDispatchComplet($onlyNonValidated = false, string $strategie = self::STRATEGIE_DATE)
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
            $demandes = self::getDemandesAvecManque();
        } else {
            $demandes = self::getDemandesTotales();
        }
        
        // Choisir la méthode de calcul selon la stratégie
        switch ($strategie) {
            case self::STRATEGIE_QUANTITE:
                $dispatch = self::calculerDispatchParQuantite($dons, $demandes);
                break;
            case self::STRATEGIE_EQUITABLE:
                $dispatch = self::calculerDispatchEquitable($dons, $demandes);
                break;
            case self::STRATEGIE_DATE:
            default:
                $dispatch = self::calculerDispatch($dons, $demandes);
                break;
        }

        return [
            'dons' => $dons,
            'demandes' => $demandes,
            'dispatch' => $dispatch
        ];
    }
}

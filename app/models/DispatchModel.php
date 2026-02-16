<?php

namespace app\models;

use Flight;

/**
 * Model pour la simulation de dispatch des dons
 * Gère l'allocation séquentielle des dons par ordre de date de demande
 */
class DispatchModel
{
    /**
     * Récupère toutes les demandes triées par date pour chaque besoin
     * Récupère aussi la quantité réelle par produit (pas par demande)
     * @return array Demandes groupées par besoin
     */
    public static function getDemandesTotales(): array
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

    /**
     * Calcule le dispatch des dons de manière séquentielle par date
     * Chaque produit d'une demande est traité individuellement
     * @param array $dons Dons disponibles ['nom_besoin' => quantite, ...]
     * @param array $demandes Toutes les demandes avec détails par produit
     * @return array Dispatch avec statuts, clé = id_besoin_ville + id_besoin combinés
     */
    public static function calculerDispatch(array $dons, array $demandes): array
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
                    $statut = 'resolved'; // ✅ Complété
                } elseif ($alloue > 0) {
                    $statut = 'partial'; // ⏳ En attente
                } else {
                    $statut = 'unresolved'; // ❌ Invalide
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

    /**
     * Récupère les données complètes pour la vue de dispatch
     * @return array ['dons' => [...], 'demandes' => [...], 'dispatch' => [...]]
     */
    public static function getDispatchComplet(): array
    {
        $donsModel = new \app\models\DashboardModel();
        $dons = $donsModel::getDonsObtenus();
        $demandes = self::getDemandesTotales();
        $dispatch = self::calculerDispatch($dons, $demandes);

        return [
            'dons' => $dons,
            'demandes' => $demandes,
            'dispatch' => $dispatch
        ];
    }
}

<?php

namespace app\models;

use flight\Engine;

class RecapModel
{
    /**
     * Récapitulatif global en montant.
     * Couvert = validé(dons) + acheté
     * Restant = total demandé - couvert
     */
    public static function getRecapMontants(Engine $app): array
    {
        $db = $app->db();

        // Besoins totaux en montant
        $montantTotal = (int) $db->query("
            SELECT COALESCE(SUM(bvd.quantite * b.prix), 0)
            FROM s3_besoin_ville_details bvd
            JOIN s3_besoin b ON bvd.id_besoin = b.id
        ")->fetchColumn();

        // Montant couvert par dons validés
        $montantValide = (int) $db->query("
            SELECT COALESCE(SUM(dv.quantite_allouee * b.prix), 0)
            FROM s3_dispatch_validation dv
            JOIN s3_besoin b ON dv.id_besoin = b.id
        ")->fetchColumn();

        // Montant couvert par achats (quantite × prix du besoin, pas le total achat avec frais)
        $montantAchete = (int) $db->query("
            SELECT COALESCE(SUM(a.quantite * b.prix), 0)
            FROM s3_achat a
            JOIN s3_besoin b ON a.id_besoin = b.id
        ")->fetchColumn();

        // Total dépensé en achats (avec frais)
        $totalDepenseAchats = (int) $db->query("
            SELECT COALESCE(SUM(total), 0) FROM s3_achat
        ")->fetchColumn();

        // Dons financiers
        $montantFinancier = (int) $db->query("
            SELECT COALESCE(SUM(montant), 0) FROM s3_don_financier
        ")->fetchColumn();

        // Solde caisse
        $soldeCaisse = $montantFinancier - $totalDepenseAchats;

        $montantCouvert = $montantValide + $montantAchete;
        $montantRestant = max(0, $montantTotal - $montantCouvert);
        $pourcentage = $montantTotal > 0
            ? min(100, round(($montantCouvert / $montantTotal) * 100, 1))
            : 0;

        return [
            'besoins_totaux'           => $montantTotal,
            'montant_valide'           => $montantValide,
            'montant_achete'           => $montantAchete,
            'besoins_satisfaits'       => $montantCouvert,
            'besoins_restants'         => $montantRestant,
            'pourcentage_satisfaction' => $pourcentage,
            'dons_financiers_total'    => $montantFinancier,
            'total_depense_achats'     => $totalDepenseAchats,
            'solde_caisse'             => $soldeCaisse,
        ];
    }

    /**
     * Détail par besoin : demandé, validé, acheté, couvert, restant.
     */
    public static function getRecapParBesoin(Engine $app): array
    {
        $sql = "
            SELECT 
                b.id,
                b.nom,
                b.prix,
                COALESCE(demandes.total_demande, 0) AS quantite_demandee,
                COALESCE(valide.total_valide, 0) AS quantite_validee,
                COALESCE(achats.total_achete, 0) AS quantite_achetee,
                COALESCE(demandes.total_demande, 0) * b.prix AS montant_demande,
                COALESCE(valide.total_valide, 0) * b.prix AS montant_valide,
                COALESCE(achats.total_achete, 0) * b.prix AS montant_achete
            FROM s3_besoin b
            LEFT JOIN (
                SELECT id_besoin, SUM(quantite) AS total_demande
                FROM s3_besoin_ville_details
                GROUP BY id_besoin
            ) demandes ON b.id = demandes.id_besoin
            LEFT JOIN (
                SELECT id_besoin, SUM(quantite_allouee) AS total_valide
                FROM s3_dispatch_validation
                GROUP BY id_besoin
            ) valide ON b.id = valide.id_besoin
            LEFT JOIN (
                SELECT id_besoin, SUM(quantite) AS total_achete
                FROM s3_achat
                GROUP BY id_besoin
            ) achats ON b.id = achats.id_besoin
            WHERE COALESCE(demandes.total_demande, 0) > 0
            ORDER BY b.nom
        ";

        $statement = $app->db()->query($sql);
        $result = [];

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $qDemande   = (int) $row['quantite_demandee'];
            $qValide    = (int) $row['quantite_validee'];
            $qAchete    = (int) $row['quantite_achetee'];
            $qCouvert   = $qValide + $qAchete;
            $qRestant   = max(0, $qDemande - $qCouvert);
            $mDemande   = (int) $row['montant_demande'];
            $mValide    = (int) $row['montant_valide'];
            $mAchete    = (int) $row['montant_achete'];
            $mCouvert   = $mValide + $mAchete;
            $mRestant   = max(0, $mDemande - $mCouvert);
            $pourcentage = $mDemande > 0
                ? min(100, round(($mCouvert / $mDemande) * 100, 1))
                : 0;

            $result[] = [
                'nom'              => $row['nom'],
                'prix'             => (int) $row['prix'],
                'quantite_demandee'=> $qDemande,
                'quantite_validee' => $qValide,
                'quantite_achetee' => $qAchete,
                'quantite_couverte'=> $qCouvert,
                'quantite_restante'=> $qRestant,
                'montant_demande'  => $mDemande,
                'montant_valide'   => $mValide,
                'montant_achete'   => $mAchete,
                'montant_couvert'  => $mCouvert,
                'montant_restant'  => $mRestant,
                'pourcentage'      => $pourcentage,
            ];
        }

        return $result;
    }
}

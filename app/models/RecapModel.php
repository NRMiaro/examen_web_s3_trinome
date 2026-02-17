<?php

namespace app\models;

use flight\Engine;

class RecapModel
{
    /**
     * Récupère le récapitulatif des besoins en montant
     * - besoins_totaux: somme (quantite demandée × prix unitaire)
     * - besoins_satisfaits: somme des quantités VALIDÉES (depuis s3_dispatch_validation)
     * - besoins_restants: somme des (montant_demande - montant_validé) pour chaque besoin
     * 
     * Formule simple: besoins_satisfaits + besoins_restants = besoins_totaux
     * 
     * @param Engine $app
     * @return array
     */
    public static function getRecapMontants(Engine $app): array
    {
        // Besoins totaux en montant (toutes les demandes des villes)
        $sqlTotaux = "
            SELECT COALESCE(SUM(bvd.quantite * b.prix), 0) AS montant_total
            FROM s3_besoin_ville_details bvd
            JOIN s3_besoin b ON bvd.id_besoin = b.id
        ";
        $stmtTotaux = $app->db()->query($sqlTotaux);
        $montantTotal = (int) $stmtTotaux->fetchColumn();

        // Besoins satisfaits en montant (UNIQUEMENT VALIDÉS)
        $sqlSatisfaits = "
            SELECT COALESCE(SUM(dv.quantite_allouee * b.prix), 0) AS montant_satisfait
            FROM s3_dispatch_validation dv
            JOIN s3_besoin b ON dv.id_besoin = b.id
        ";
        $stmtSatisfaits = $app->db()->query($sqlSatisfaits);
        $montantSatisfaitNature = (int) $stmtSatisfaits->fetchColumn();

        // Besoins RESTANTS = somme des (demande - validée) pour chaque besoin
        // Formule: besoins_totaux - besoins_satisfaits
        $montantRestant = max(0, $montantTotal - $montantSatisfaitNature);

        // Dons financiers (informationnel)
        $sqlFinancier = "
            SELECT COALESCE(SUM(df.montant), 0) AS montant_financier
            FROM s3_don_financier df
        ";
        $stmtFinancier = $app->db()->query($sqlFinancier);
        $montantFinancierBrut = (int) $stmtFinancier->fetchColumn();

        // Pourcentage de satisfaction (plafonné à 100%)
        $pourcentage = $montantTotal > 0 
            ? min(100, round(($montantSatisfaitNature / $montantTotal) * 100, 1))
            : 0;

        return [
            'besoins_totaux' => $montantTotal,
            'besoins_satisfaits' => $montantSatisfaitNature,
            'besoins_satisfaits_nature' => $montantSatisfaitNature,
            'besoins_satisfaits_financier' => 0, // Les dons financiers sont informationnels
            'besoins_restants' => $montantRestant,
            'pourcentage_satisfaction' => $pourcentage,
            // Infos supplémentaires pour transparence
            'dons_financiers_total' => $montantFinancierBrut
        ];
    }

    /**
     * Récupère le détail par type de besoin
     * Les quantités satisfaites viennent de s3_dispatch_validation (validées uniquement)
     * 
     * @param Engine $app
     * @return array
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
                COALESCE(demandes.total_demande, 0) * b.prix AS montant_demande,
                COALESCE(valide.total_valide, 0) * b.prix AS montant_valide
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
            ORDER BY b.nom
        ";
        
        $statement = $app->db()->query($sql);
        $result = [];
        
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $montantDemande = (int) $row['montant_demande'];
            $montantValide = (int) $row['montant_valide'];
            
            $row['quantite_donnee'] = (int) $row['quantite_validee'];
            $row['montant_donne'] = $montantValide;
            $row['montant_restant'] = max(0, $montantDemande - $montantValide);
            $row['pourcentage'] = $montantDemande > 0 
                ? min(100, round(($montantValide / $montantDemande) * 100, 1))
                : 0;
            $result[] = $row;
        }
        
        return $result;
    }
}

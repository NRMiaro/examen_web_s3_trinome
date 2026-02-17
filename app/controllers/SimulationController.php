<?php

namespace app\controllers;

use Flight;
use app\models\DashboardModel;
use app\models\DispatchModel;


class SimulationController
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index()
    {
        // Récupérer la stratégie choisie (par défaut : date)
        $strategie = Flight::request()->query->strategie ?? DispatchModel::STRATEGIE_DATE;
        $strategiesDisponibles = DispatchModel::getStrategiesDisponibles();
        if (!isset($strategiesDisponibles[$strategie])) {
            $strategie = DispatchModel::STRATEGIE_DATE;
        }

        // Récupérer les données de base
        $liste_villes = DashboardModel::getListeVilles();
        $total_demande = DashboardModel::getTotalDemandeParBesoin();
        
        // Couverture réelle de TOUTES les demandes (validé + acheté)
        $fullCoverage = DashboardModel::getDispatchValide();
        
        // Simuler le dispatch théorique avec les dons restants + stratégie choisie
        $dispatch_data = DispatchModel::getDispatchComplet(true, $strategie);
        
        // Stock disponible pour la simulation (après soustraction des validations)
        $dons_disponibles = $dispatch_data['dons'];
        
        // Créer un index pour accéder rapidement au statut de dispatch simulé
        $dispatchIndex = [];
        foreach ($dispatch_data['dispatch'] as $item) {
            $cle = $item['id_besoin_ville'] . '_' . $item['id_besoin'];
            $dispatchIndex[$cle] = $item;
        }
        
        // Calculer les restes après simulation du dispatch
        $donsRestants = $dons_disponibles;
        foreach ($dispatch_data['dispatch'] as $item) {
            $besoinNom = $item['besoin_nom'];
            if (isset($donsRestants[$besoinNom])) {
                $donsRestants[$besoinNom] -= $item['alloue'];
            }
        }
        
        // Filtrer besoinsVilles : garder seulement les produits NON 100% couverts
        $besoinsVillesToutes = DashboardModel::getBesoinsParVille();
        $besoinsVilles = [];
        foreach ($besoinsVillesToutes as $villeData) {
            $demandesFiltrees = [];
            foreach ($villeData['demandes'] as $demande) {
                $produitsFiltres = [];
                foreach ($demande['produits'] as $produit) {
                    $key = $demande['id_besoin_ville'] . '_' . $produit['id_besoin'];
                    $covStatut = $fullCoverage[$key]['statut'] ?? null;
                    if ($covStatut !== 'resolved') {
                        $produitsFiltres[] = $produit;
                    }
                }
                if (!empty($produitsFiltres)) {
                    $demande['produits'] = $produitsFiltres;
                    $demandesFiltrees[] = $demande;
                }
            }
            if (!empty($demandesFiltrees)) {
                $villeData['demandes'] = $demandesFiltrees;
                $besoinsVilles[] = $villeData;
            }
        }

        // Passer les données à la vue
        Flight::render('simulation/index', [
            'page_title'       => 'Simulation de Dispatch',
            'active_menu'      => 'simulation',
            'liste_villes'     => $liste_villes,
            'dons_disponibles' => $dons_disponibles,
            'dons_restants'    => $donsRestants,
            'besoinsVilles'    => $besoinsVilles,
            'total_demande'    => $total_demande,
            'dispatch'         => $dispatchIndex,
            'coverage'         => $fullCoverage,
            'strategie'        => $strategie,
            'strategies'       => $strategiesDisponibles,
        ]);
    }

    public function valider()
    {
        $db = Flight::db();
        
        try {
            // Lire la stratégie choisie depuis le formulaire POST
            $strategie = Flight::request()->data->strategie ?? DispatchModel::STRATEGIE_DATE;
            $strategiesDisponibles = DispatchModel::getStrategiesDisponibles();
            if (!isset($strategiesDisponibles[$strategie])) {
                $strategie = DispatchModel::STRATEGIE_DATE;
            }

            // Valider seulement les demandes avec manque restant, avec la stratégie choisie
            $dispatch_data = DispatchModel::getDispatchComplet(true, $strategie);
            
            // Commencer une transaction
            $db->beginTransaction();
            
            // Préparer les requêtes
            $stmtCheck = $db->prepare("
                SELECT id, quantite_allouee FROM s3_dispatch_validation 
                WHERE id_besoin_ville = :id_besoin_ville AND id_besoin = :id_besoin
            ");
            
            $stmtUpdate = $db->prepare("
                UPDATE s3_dispatch_validation 
                SET quantite_allouee = :quantite_allouee, 
                    quantite_manquante = :quantite_manquante, 
                    statut = :statut,
                    date_validation = CURRENT_TIMESTAMP
                WHERE id_besoin_ville = :id_besoin_ville AND id_besoin = :id_besoin
            ");
            
            $stmtInsert = $db->prepare("
                INSERT INTO s3_dispatch_validation 
                (id_besoin_ville, id_besoin, quantite_demandee, quantite_allouee, quantite_manquante, statut)
                VALUES (:id_besoin_ville, :id_besoin, :quantite_demandee, :quantite_allouee, :quantite_manquante, :statut)
            ");
            
            // Insérer ou mettre à jour chaque ligne de dispatch
            foreach ($dispatch_data['dispatch'] as $item) {
                $nouvelAlloue = $item['alloue'];
                
                // Vérifier si une validation existe déjà
                $stmtCheck->execute([
                    ':id_besoin_ville' => $item['id_besoin_ville'],
                    ':id_besoin'       => $item['id_besoin'],
                ]);
                $existant = $stmtCheck->fetch(\PDO::FETCH_ASSOC);
                
                if ($existant) {
                    // Mise à jour : cumuler l'ancien alloué + le nouveau
                    $totalAlloue = (int)$existant['quantite_allouee'] + $nouvelAlloue;
                    $quantiteDemandeeOriginale = $item['quantite_demandee'] + (int)$existant['quantite_allouee'];
                    $manquant = $quantiteDemandeeOriginale - $totalAlloue;
                    if ($manquant < 0) $manquant = 0;
                    
                    $statut = $manquant === 0 ? 'resolved' : ($totalAlloue > 0 ? 'partial' : 'unresolved');
                    
                    $stmtUpdate->execute([
                        ':quantite_allouee'    => $totalAlloue,
                        ':quantite_manquante'  => $manquant,
                        ':statut'              => $statut,
                        ':id_besoin_ville'     => $item['id_besoin_ville'],
                        ':id_besoin'           => $item['id_besoin'],
                    ]);
                } else {
                    // Nouvelle insertion
                    $quantite_demandee = $item['quantite_demandee'];
                    $quantite_manquante = $quantite_demandee - $nouvelAlloue;
                    if ($quantite_manquante < 0) $quantite_manquante = 0;
                    
                    $statut = $quantite_manquante === 0 ? 'resolved' : ($nouvelAlloue > 0 ? 'partial' : 'unresolved');
                    
                    $stmtInsert->execute([
                        ':id_besoin_ville'     => $item['id_besoin_ville'],
                        ':id_besoin'           => $item['id_besoin'],
                        ':quantite_demandee'   => $quantite_demandee,
                        ':quantite_allouee'    => $nouvelAlloue,
                        ':quantite_manquante'  => $quantite_manquante,
                        ':statut'              => $statut,
                    ]);
                }
            }
            
            // Valider la transaction
            $db->commit();
            
            // Rediriger vers la simulation avec la stratégie utilisée
            Flight::redirect('/simulation?success=dispatch_valide&strategie=' . urlencode($strategie));
            
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            // Rediriger avec message d'erreur
            Flight::redirect(BASE_URL . '/simulation?error=validation_failed');
        }
    }
}

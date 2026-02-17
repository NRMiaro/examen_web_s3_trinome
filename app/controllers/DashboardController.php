<?php

namespace app\controllers;

use Flight;
use app\models\DashboardModel;
use app\models\DispatchModel;

class DashboardController
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index()
    {
        // Récupérer les données depuis le Model
        $liste_villes = DashboardModel::getListeVilles();
        $dons_disponibles = DashboardModel::getDonsObtenus();
        $besoinsVillesToutes = DashboardModel::getBesoinsParVille();
        $total_demande = DashboardModel::getTotalDemandeParBesoin();
        
        // Récupérer le dispatch VALIDÉ depuis la BD
        $dispatchIndex = DashboardModel::getDispatchValide();
        
        // Filtrer : ne garder que les demandes/produits couverts à 100% (resolved)
        $besoinsVilles = [];
        foreach ($besoinsVillesToutes as $villeData) {
            $demandesFiltrees = [];
            foreach ($villeData['demandes'] as $demande) {
                $produitsFiltres = [];
                foreach ($demande['produits'] as $produit) {
                    $cle = $demande['id_besoin_ville'] . '_' . $produit['id_besoin'];
                    if (isset($dispatchIndex[$cle]) && $dispatchIndex[$cle]['statut'] === 'resolved') {
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
        
        // Calculer les dons matériels restants (dons bruts - quantités validées par dispatch)
        $dons_alloues = DashboardModel::getDonsMontantsValidees();
        $donsRestants = $dons_disponibles;
        foreach ($dons_alloues as $besoinNom => $montant) {
            if (isset($donsRestants[$besoinNom])) {
                $donsRestants[$besoinNom] -= $montant;
                if ($donsRestants[$besoinNom] < 0) {
                    $donsRestants[$besoinNom] = 0;
                }
            }
        }

        // Passer les données à la vue
        Flight::render('dashboard', [
            'page_title'       => 'Tableau de bord',
            'active_menu'      => 'dashboard',
            'liste_villes'     => $liste_villes,
            'dons_disponibles' => $dons_disponibles,
            'dons_restants'    => $donsRestants,
            'besoinsVilles'    => $besoinsVilles,
            'total_demande'    => $total_demande,
            'dispatch'         => $dispatchIndex,
        ]);
    }
}

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
        // Récupérer les données depuis le Model (utilise la vue v_qte_dons_obtenus)
        $liste_villes = DashboardModel::getListeVilles();
        $dons_disponibles = DashboardModel::getDonsObtenus();
        $besoinsVilles = DashboardModel::getBesoinsParVille();
        $total_demande = DashboardModel::getTotalDemandeParBesoin();
        
        // Récupérer les données de dispatch
        $dispatch_data = DispatchModel::getDispatchComplet();
        
        // Créer un index pour accéder rapidement au statut de dispatch par (id_besoin_ville + id_besoin)
        $dispatchIndex = [];
        foreach ($dispatch_data['dispatch'] as $item) {
            // Trouver l'id_besoin correspondant à ce produit
            $cle = $item['id_besoin_ville'] . '_' . $item['id_besoin'];
            $dispatchIndex[$cle] = $item;
        }
        
        // Calculer les restes après dispatch (dons non alloués)
        $donsRestants = $dons_disponibles; // Copie des dons initiaux
        foreach ($dispatch_data['dispatch'] as $item) {
            $besoinNom = $item['besoin_nom'];
            if (isset($donsRestants[$besoinNom])) {
                $donsRestants[$besoinNom] -= $item['alloue'];
            }
        }

        // Passer les données à la vue
        Flight::render('dashboard', [
            'page_title'       => 'Tableau de bord',
            'liste_villes'     => $liste_villes,
            'dons_disponibles' => $dons_disponibles,
            'dons_restants'    => $donsRestants,
            'besoinsVilles'    => $besoinsVilles,
            'total_demande'    => $total_demande,
            'dispatch'         => $dispatchIndex,
        ]);
    }
}

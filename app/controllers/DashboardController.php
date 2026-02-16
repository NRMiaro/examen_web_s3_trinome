<?php

namespace app\controllers;

use Flight;
use app\models\DashboardModel;

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
        $dons_disponibles = DashboardModel::getDonsObtenus();
        $villes = DashboardModel::getBesoinsParVille();
        $total_demande = DashboardModel::getTotalDemandeParBesoin();

        // Passer les données à la vue
        Flight::render('dashboard', [
            'page_title'       => 'Tableau de bord',
            'dons_disponibles' => $dons_disponibles,
            'villes'           => $villes,
            'total_demande'    => $total_demande,
        ]);
    }
}

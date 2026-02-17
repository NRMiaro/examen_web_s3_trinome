<?php

namespace app\controllers;

use flight\Engine;
use app\models\RecapModel;

class RecapController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $recap = RecapModel::getRecapMontants($this->app);
        $recapParBesoin = RecapModel::getRecapParBesoin($this->app);

        $this->app->render('recap/index', [
            'page_title' => 'Récapitulation',
            'active_menu' => 'recap',
            'recap' => $recap,
            'recap_par_besoin' => $recapParBesoin
        ]);
    }

    public function apiData(): void
    {
        $recap = RecapModel::getRecapMontants($this->app);
        $recapParBesoin = RecapModel::getRecapParBesoin($this->app);

        $this->app->json([
            'success' => true,
            'data' => [
                'recap' => $recap,
                'recap_par_besoin' => $recapParBesoin
            ]
        ]);
    }
}

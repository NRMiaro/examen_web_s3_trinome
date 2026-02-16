<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\models\CaisseModel;

class CaisseController
{
    protected Engine $app;
    protected CaisseModel $model;

    public function __construct($app)
    {
        $this->app = $app;
        $this->model = new CaisseModel(Flight::db());
    }

    public function index()
    {
        $search = Flight::request()->query->search ?? '';
        
        if (!empty($search)) {
            $caisses = $this->model->searchCaisse($search);
        } else {
            $caisses = $this->model->getAllCaisse();
        }

        $total = $this->model->getTotalCaisse();
        
        Flight::render('caisse/index', [
            'page_title'  => 'Caisse',
            'caisses'     => $caisses,
            'total'       => $total,
            'search'      => $search,
        ]);
    }
}

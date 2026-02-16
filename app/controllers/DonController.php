<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\models\DonModel;

class DonController
{
    protected Engine $app;
    protected DonModel $model;

    public function __construct($app)
    {
        $this->app = $app;
        $this->model = new DonModel(Flight::db());
    }

    public function index()
    {
        $search = Flight::request()->query->search ?? '';
        $date_debut = Flight::request()->query->date_debut ?? '';
        $date_fin = Flight::request()->query->date_fin ?? '';
        
        if (!empty($search) || !empty($date_debut) || !empty($date_fin)) {
            $dons = $this->model->searchDons($search, $date_debut, $date_fin);
        } else {
            $dons = $this->model->getAllDons();
        }

        Flight::render('dons/index', [
            'page_title'  => 'Dons',
            'active_menu' => 'dons',
            'dons'        => $dons,
            'search'      => $search,
            'date_debut'  => $date_debut,
            'date_fin'    => $date_fin,
        ]);
    }

    public function create()
    {
        $all_besoins = $this->model->getAllBesoins();
        Flight::render('dons/form', [
            'page_title'  => 'Nouveau don',
            'active_menu' => 'dons',
            'action'      => BASE_URL . '/dons',
            'all_besoins' => $all_besoins,
        ]);
    }

    public function store()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            $data = [
                'type_don'  => $request->data->type_don,
                'date_don'  => $request->data->date_don,
                'montant'   => $request->data->montant,
                'besoins'   => $request->data->besoins,
                'quantites' => $request->data->quantites,
            ];
            $this->model->insertDon($data);
            Flight::redirect('/dons');
        }
    }
}

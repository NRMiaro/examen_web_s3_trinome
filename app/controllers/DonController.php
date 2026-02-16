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

        $types_don = $this->model->getAllTypesDon();
        
        Flight::render('dons/index', [
            'page_title'  => 'Dons',
            'dons'        => $dons,
            'types_don'   => $types_don,
            'search'      => $search,
            'date_debut'  => $date_debut,
            'date_fin'    => $date_fin,
        ]);
    }

    public function create()
    {
        $types_don = $this->model->getAllTypesDon();
        $all_besoins = $this->model->getAllBesoins();
        Flight::render('dons/form', [
            'page_title'  => 'Nouveau don',
            'action'      => BASE_URL . '/dons',
            'types_don'   => $types_don,
            'all_besoins' => $all_besoins,
        ]);
    }

    public function store()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            $data = $request->data->getData();
            $this->model->insertDon($data);
            Flight::redirect('dons');
        }
    }
}

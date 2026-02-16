<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\models\BesoinModel;

class BesoinController
{
    protected Engine $app;
    protected BesoinModel $model;

    public function __construct($app)
    {
        $this->app = $app;
        $this->model = new BesoinModel(Flight::db());
    }

    public function index()
    {
        $besoins = $this->model->getAllBesoins();
        Flight::render('besoins/index', [
            'page_title'  => 'Besoins',
            'besoins'     => $besoins,
        ]);
    }

    public function create()
    {
        $types = $this->model->getAllTypes();
        Flight::render('besoins/form', [
            'page_title'  => 'Nouveau besoin',
            'action'      => BASE_URL . '/besoins',
            'types'       => $types,
        ]);
    }

    public function store()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            $data = $request->data->getData();
            $this->model->insertBesoin($data);
            Flight::redirect('/besoins');
        }
    }

    public function edit($id)
    {
        $besoin = $this->model->getBesoinById($id);
        
        if (!$besoin) {
            Flight::redirect('/besoins');
            return;
        }

        $types = $this->model->getAllTypes();
        Flight::render('besoins/form', [
            'page_title'  => 'Modifier besoin',
            'action'      => BASE_URL . '/besoins/' . $id,
            'besoin'      => $besoin,
            'types'       => $types,
        ]);
    }

    public function update($id)
    {
        $request = Flight::request();
        $data = $request->data->getData();
        $this->model->updateBesoin($id, $data);
        Flight::redirect('/besoins');
    }

    public function delete($id)
    {
        $this->model->deleteBesoin($id);
        Flight::redirect('/besoins');
    }
}

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
        $villes = $this->model->getAllVilles();
        $types = $this->model->getAllTypes();
        Flight::render('besoins/form', [
            'page_title'  => 'Nouveau besoin',
            'action'      => BASE_URL . '/besoins',
            'villes'      => $villes,
            'types'       => $types,
            'date_besoin' => date('Y-m-d\TH:i'),
        ]);
    }

    public function store()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            $idVille = $request->data->id_ville;
            $dateBesoin = $request->data->date_besoin;
            $nomBesoins = $request->data->{'nom_besoin'} ?? [];
            $typeBesoins = $request->data->{'id_type_besoin'} ?? [];
            $prixBesoins = $request->data->{'prix_besoin'} ?? [];
            $quantites = $request->data->quantite ?? [];

            if (!$idVille || !$dateBesoin || empty($nomBesoins)) {
                Flight::redirect('/besoins/nouveau');
                return;
            }

            // Créer les besoins et ajouter à la ville
            $besoins = [];
            foreach ($nomBesoins as $index => $nom) {
                if (!empty($nom) && isset($typeBesoins[$index]) && isset($prixBesoins[$index]) && isset($quantites[$index])) {
                    // Créer ou récupérer le besoin
                    $idBesoin = $this->model->getOrCreateBesoin(
                        $nom,
                        $typeBesoins[$index],
                        $prixBesoins[$index]
                    );

                    if ($quantites[$index] > 0) {
                        $besoins[] = [
                            'id_besoin' => $idBesoin,
                            'quantite' => $quantites[$index]
                        ];
                    }
                }
            }

            if (!empty($besoins)) {
                $this->model->createBesoinVille($idVille, $dateBesoin, $besoins);
            }

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

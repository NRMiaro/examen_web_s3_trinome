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
        $search = Flight::request()->query->search ?? '';
        
        if (!empty($search)) {
            $besoins = $this->model->searchBesoins($search);
        } else {
            $besoins = $this->model->getAllBesoins();
        }
        
        Flight::render('besoins/index', [
            'page_title'  => 'Besoins',
            'besoins'     => $besoins,
            'search'      => $search,
        ]);
    }

    public function create()
    {
        $villes = $this->model->getAllVilles();
        $besoins = $this->model->getAllBesoins();
        Flight::render('besoins/form', [
            'page_title'  => 'Nouvelle demande de besoin',
            'action'      => BASE_URL . '/besoins',
            'villes'      => $villes,
            'besoins'     => $besoins,
            'date_besoin' => date('Y-m-d\TH:i'),
        ]);
    }

    public function creer()
    {
        $types = $this->model->getAllTypes();
        Flight::render('besoins/creer', [
            'page_title'  => 'Ajouter un besoin',
            'action'      => BASE_URL . '/besoins/creer',
            'types'       => $types,
        ]);
    }

    public function storeBesoin()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            // Créer un nouveau besoin
            if (isset($request->data->nom) && isset($request->data->id_type_besoin) && isset($request->data->prix)) {
                $this->model->insertBesoin([
                    'nom' => $request->data->nom,
                    'id_type_besoin' => $request->data->id_type_besoin,
                    'prix' => $request->data->prix,
                ]);
                Flight::redirect('/besoins');
            } else {
                Flight::redirect('/besoins/creer');
            }
        }
    }

    public function storeDemandeVille()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            // Créer une demande de besoin pour une ville
            $idVille = $request->data->id_ville;
            $dateBesoin = $request->data->date_besoin;
            $idBesoins = $request->data->{'id_besoin'} ?? [];
            $quantites = $request->data->quantite ?? [];

            // Validation des données
            if (!$idVille || !$dateBesoin || empty($idBesoins) || $dateBesoin === 'creer') {
                Flight::redirect('/besoins/nouveau');
                return;
            }

            // Valider le format de la date
            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i', $dateBesoin);
            if (!$dateTime) {
                // Essayer d'autres formats de date
                $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateBesoin);
                if (!$dateTime) {
                    Flight::redirect('/besoins/nouveau');
                    return;
                }
            }
            
            // Convertir au format MySQL
            $dateBesoin = $dateTime->format('Y-m-d H:i:s');

            // Construire le tableau des besoins
            $besoins = [];
            foreach ($idBesoins as $index => $idBesoin) {
                if (isset($quantites[$index]) && $quantites[$index] > 0) {
                    $besoins[] = [
                        'id_besoin' => $idBesoin,
                        'quantite' => $quantites[$index]
                    ];
                }
            }

            if (!empty($besoins)) {
                $this->model->createBesoinVille($idVille, $dateBesoin, $besoins);
            }

            Flight::redirect('/besoins');
        }
    }

    public function store()
    {
        // Méthode dépréciée - rediriger vers les nouvelles méthodes
        Flight::redirect('/besoins');
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
        $result = $this->model->deleteBesoin($id);
        
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!$result['success']) {
            $_SESSION['error_message'] = $result['message'];
        } else {
            $_SESSION['success_message'] = $result['message'];
        }
        
        Flight::redirect('/besoins');
    }
}

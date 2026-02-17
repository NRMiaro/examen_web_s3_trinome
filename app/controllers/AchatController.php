<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\models\AchatModel;

class AchatController
{
    protected Engine $app;
    protected AchatModel $model;

    public function __construct($app)
    {
        $this->app = $app;
        $this->model = new AchatModel(Flight::db());
    }

    public function index()
    {
        $id_ville = Flight::request()->query->id_ville ?? '';
        
        $achats = $this->model->getAllAchats($id_ville);
        $villes = $this->model->getAllVilles();
        $solde = $this->model->getSoldeCaisse();

        Flight::render('achats/index', [
            'page_title'  => 'Achats',
            'active_menu' => 'achats',
            'achats'      => $achats,
            'villes'      => $villes,
            'id_ville'    => $id_ville,
            'solde'       => $solde,
        ]);
    }

    public function create()
    {
        $besoinsRestants = $this->model->getBesoinsRestants();
        $villes = $this->model->getAllVilles();
        $solde = $this->model->getSoldeCaisse();
        
        // Frais configurable depuis config.php
        $frais_pourcent = Flight::app()->get('frais_achat') ?? 10;

        Flight::render('achats/form', [
            'page_title'      => 'Nouvel achat',
            'active_menu'     => 'achats',
            'besoinsRestants' => $besoinsRestants,
            'villes'          => $villes,
            'solde'           => $solde,
            'frais_pourcent'  => $frais_pourcent,
        ]);
    }

    public function store()
    {
        $request = Flight::request();
        
        if ($request->method === 'POST') {
            $id_besoin = $request->data->id_besoin;
            $id_ville = $request->data->id_ville;
            $quantite = (int) $request->data->quantite;
            $prix_unitaire = (int) $request->data->prix_unitaire;
            $frais_pourcent = (float) (Flight::app()->get('frais_achat') ?? 10);

            // Calculer le total avec frais
            $sous_total = $quantite * $prix_unitaire;
            $total = (int) ceil($sous_total * (1 + $frais_pourcent / 100));

            // Vérifier si le besoin est déjà disponible dans les dons restants
            if ($this->model->besoinDejaDisponible($id_besoin, $quantite)) {
                $besoinsRestants = $this->model->getBesoinsRestants();
                $villes = $this->model->getAllVilles();
                $solde = $this->model->getSoldeCaisse();
                
                Flight::render('achats/form', [
                    'page_title'      => 'Nouvel achat',
                    'active_menu'     => 'achats',
                    'besoinsRestants' => $besoinsRestants,
                    'villes'          => $villes,
                    'solde'           => $solde,
                    'frais_pourcent'  => $frais_pourcent,
                    'error'           => 'Ce besoin est encore disponible dans les dons restants. Achat inutile.',
                ]);
                return;
            }

            // Vérifier le solde de la caisse
            $solde = $this->model->getSoldeCaisse();
            if ($total > $solde['solde']) {
                $besoinsRestants = $this->model->getBesoinsRestants();
                $villes = $this->model->getAllVilles();

                Flight::render('achats/form', [
                    'page_title'      => 'Nouvel achat',
                    'active_menu'     => 'achats',
                    'besoinsRestants' => $besoinsRestants,
                    'villes'          => $villes,
                    'solde'           => $solde,
                    'frais_pourcent'  => $frais_pourcent,
                    'error'           => 'Solde insuffisant. Solde actuel : ' . number_format($solde['solde'], 0, ',', ' ') . ' Ar. Total achat : ' . number_format($total, 0, ',', ' ') . ' Ar.',
                ]);
                return;
            }

            $this->model->insertAchat([
                'id_besoin' => $id_besoin,
                'id_ville' => $id_ville,
                'quantite' => $quantite,
                'prix_unitaire' => $prix_unitaire,
                'frais_pourcent' => $frais_pourcent,
                'total' => $total,
                'date_achat' => date('Y-m-d H:i:s'),
            ]);

            Flight::redirect('/achats');
        }
    }

    public function delete($id)
    {
        $this->model->deleteAchat($id);
        Flight::redirect('/achats');
    }
}

<?php

namespace app\controllers;

use flight\Engine;

class BesoinController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $this->app->render('besoins/index', [
            'page_title'  => 'Besoins',
            'active_menu' => 'besoins',
        ]);
    }

    public function create(): void
    {
        $this->app->render('besoins/form', [
            'page_title'  => 'Nouveau besoin',
            'active_menu' => 'besoins',
            'action'      => '/besoins',
            'method'      => 'POST',
        ]);
    }

    public function store(): void
    {
        // TODO: Logique d'enregistrement du besoin
        $this->app->redirect('/besoins');
    }

    public function edit(string $id): void
    {
        $this->app->render('besoins/form', [
            'page_title'  => 'Modifier besoin',
            'active_menu' => 'besoins',
            'action'      => '/besoins/' . $id,
            'method'      => 'PUT',
            'besoin_id'   => $id,
        ]);
    }

    public function update(string $id): void
    {
        // TODO: Logique de mise à jour du besoin
        $this->app->redirect('/besoins');
    }

    public function delete(string $id): void
    {
        // TODO: Logique de suppression du besoin
        $this->app->redirect('/besoins');
    }
}

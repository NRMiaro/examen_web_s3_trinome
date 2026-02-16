<?php

namespace app\controllers;

use flight\Engine;

class DonController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $this->app->render('dons/index', [
            'page_title'  => 'Dons',
            'active_menu' => 'dons',
        ]);
    }

    public function create(): void
    {
        $this->app->render('dons/form', [
            'page_title'  => 'Nouveau don',
            'active_menu' => 'dons',
            'action'      => '/dons',
            'method'      => 'POST',
        ]);
    }

    public function store(): void
    {
        // TODO: Logique d'enregistrement du don
        $this->app->redirect('/dons');
    }

    public function edit(string $id): void
    {
        $this->app->render('dons/form', [
            'page_title'  => 'Modifier don',
            'active_menu' => 'dons',
            'action'      => '/dons/' . $id,
            'method'      => 'PUT',
            'don_id'      => $id,
        ]);
    }

    public function update(string $id): void
    {
        // TODO: Logique de mise à jour du don
        $this->app->redirect('/dons');
    }

    public function delete(string $id): void
    {
        // TODO: Logique de suppression du don
        $this->app->redirect('/dons');
    }
}

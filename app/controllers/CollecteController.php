<?php

namespace app\controllers;

use flight\Engine;

class CollecteController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $this->app->render('collectes', [
            'page_title'  => 'Collectes',
            'active_menu' => 'collectes',
        ]);
    }

    public function create(): void
    {
        $this->app->render('form-collecte', [
            'page_title'  => 'Nouvelle collecte',
            'active_menu' => 'collectes',
        ]);
    }
}

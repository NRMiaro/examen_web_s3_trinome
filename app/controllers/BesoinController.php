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
        $this->app->render('besoins', [
            'page_title'  => 'Besoins',
            'active_menu' => 'besoins',
        ]);
    }
}

<?php

namespace app\controllers;

use flight\Engine;

class DashboardController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $this->app->render('dashboard', [
            'page_title'  => 'Tableau de bord',
            'active_menu' => 'dashboard',
        ]);
    }
}

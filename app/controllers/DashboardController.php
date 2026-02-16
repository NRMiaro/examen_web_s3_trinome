<?php

namespace app\controllers;

use Flight;

class DashboardController
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index()
    {
        Flight::render('dashboard', [
            'page_title'  => 'Tableau de bord',
        ]);
    }
}

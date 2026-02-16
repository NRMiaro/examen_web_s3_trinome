<?php

namespace app\controllers;

use flight\Engine;

class DistributionController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $this->app->render('distributions', [
            'page_title'  => 'Distributions',
            'active_menu' => 'distributions',
        ]);
    }

    public function create(): void
    {
        $this->app->render('stepper', [
            'page_title'  => 'Nouvelle distribution',
            'active_menu' => 'distributions',
        ]);
    }
}

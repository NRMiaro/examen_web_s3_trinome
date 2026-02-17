<?php

use app\controllers\DashboardController;
use app\controllers\BesoinController;
use app\controllers\DonController;
use app\controllers\CaisseController;
use app\controllers\AchatController;
use app\controllers\SimulationController;
use app\controllers\RecapController;
use app\models\DonModel;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;
use flight\Engine;

$router->group('', function (Router $router) use ($app) {

    // Dashboard
    $router->get('/', function () use ($app) {
        $controller = new DashboardController($app);
        $controller->index();
    });

    // Simulation
    $router->get('/simulation', function () use ($app) {
        $controller = new SimulationController($app);
        $controller->index();
    });

    $router->post('/simulation/valider', function () use ($app) {
        $controller = new SimulationController($app);
        $controller->valider();
    });

    $router->post('/simulation/reset', function () use ($app) {
        $controller = new SimulationController($app);
        $controller->reset();
    });

    // CRUD Besoins
    $router->group('/besoins', function () use ($router, $app) {
        $router->get('', function () use ($app) {
            $controller = new BesoinController($app);
            $controller->index();
        });

        $router->get('/nouveau', function () use ($app) {
            $controller = new BesoinController($app);
            $controller->create();
        });

        $router->get('/creer', function () use ($app) {
            $controller = new BesoinController($app);
            $controller->creer();
        });

        $router->post('', function () use ($app) {
            $controller = new BesoinController($app);
            $controller->store();
        });

        $router->get('/@id/modifier', function ($id) use ($app) {
            $controller = new BesoinController($app);
            $controller->edit($id);
        });

        $router->post('/@id', function ($id) use ($app) {
            $controller = new BesoinController($app);
            $controller->update($id);
        });

        $router->post('/@id/supprimer', function ($id) use ($app) {
            $controller = new BesoinController($app);
            $controller->delete($id);
        });
    });

    // CRUD Dons
    $router->group('/dons', function () use ($router, $app) {
        $router->get('', function () use ($app) {
            $controller = new DonController($app);
            $controller->index();
        });

        $router->get('/nouveau', function () use ($app) {
            $controller = new DonController($app);
            $controller->create();
        });

        $router->post('', function () use ($app) {
            $controller = new DonController($app);
            $controller->store();
        });
    });

    // API Routes
    $router->group('/api', function () use ($router, $app) {
        $router->get('/besoins-by-type', function () use ($app) {
            $type = Flight::request()->query->type ?? '';
            $model = new DonModel(Flight::db());
            $besoins = !empty($type) ? $model->getBesoinsByType($type) : [];
            Flight::json(['data' => $besoins]);
        });

        $router->get('/recap', function () use ($app) {
            $controller = new RecapController($app);
            $controller->apiData();
        });
    });

    // Récapitulation
    $router->get('/recap', function () use ($app) {
        $controller = new RecapController($app);
        $controller->index();
    });

    // CRUD Caisse
    $router->group('/caisse', function () use ($router, $app) {
        $router->get('', function () use ($app) {
            $controller = new CaisseController($app);
            $controller->index();
        });
    });

    // CRUD Achats
    $router->group('/achats', function () use ($router, $app) {
        $router->get('', function () use ($app) {
            $controller = new AchatController($app);
            $controller->index();
        });

        $router->get('/nouveau', function () use ($app) {
            $controller = new AchatController($app);
            $controller->create();
        });

        $router->post('', function () use ($app) {
            $controller = new AchatController($app);
            $controller->store();
        });

        $router->post('/@id/supprimer', function ($id) use ($app) {
            $controller = new AchatController($app);
            $controller->delete($id);
        });
    });

}, [SecurityHeadersMiddleware::class]);


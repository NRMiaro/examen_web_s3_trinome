<?php

use app\controllers\DashboardController;
use app\controllers\BesoinController;
use app\controllers\DonController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;
use flight\Engine;

$router->group('', function (Router $router) use ($app) {

    // Dashboard
    $router->get('/', function () use ($app) {
        $controller = new DashboardController($app);
        $controller->index();
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

        $router->get('/@id/modifier', function ($id) use ($app) {
            $controller = new DonController($app);
            $controller->edit($id);
        });

        $router->post('/@id', function ($id) use ($app) {
            $controller = new DonController($app);
            $controller->update($id);
        });

        $router->post('/@id/supprimer', function ($id) use ($app) {
            $controller = new DonController($app);
            $controller->delete($id);
        });
    });

}, [SecurityHeadersMiddleware::class]);


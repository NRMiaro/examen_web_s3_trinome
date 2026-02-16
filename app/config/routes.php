<?php

use app\controllers\DashboardController;
use app\controllers\CollecteController;
use app\controllers\DistributionController;
use app\controllers\BesoinController;
use app\controllers\ApiExampleController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;
use flight\Engine;

$router->group('', function (Router $router) use ($app) {

    $router->get('/', function () use ($app) {
        $controller = new DashboardController($app);
        $controller->index();
    });

    $router->group('/collectes', function () use ($router, $app) {

        $router->get('', function () use ($app) {
            $controller = new CollecteController($app);
            $controller->index();
        });

        $router->get('/nouveau', function () use ($app) {
            $controller = new CollecteController($app);
            $controller->create();
        });
    });

    $router->group('/distributions', function () use ($router, $app) {

        $router->get('', function () use ($app) {
            $controller = new DistributionController($app);
            $controller->index();
        });

        $router->get('/nouveau', function () use ($app) {
            $controller = new DistributionController($app);
            $controller->create();
        });
    });

    $router->get('/besoins', function () use ($app) {
        $controller = new BesoinController($app);
        $controller->index();
    });

}, [SecurityHeadersMiddleware::class]);

<?php
$ds = DIRECTORY_SEPARATOR;

// Autoload
require __DIR__ . $ds . '..' . $ds . '..' . $ds . 'vendor' . $ds . 'autoload.php';

// Vérif config
if (!file_exists(__DIR__ . $ds . 'config.php')) {
    Flight::halt(500, 'Config file not found.');
}

// App
$app = Flight::app();

// Config
$config = require __DIR__ . $ds . 'config.php';

// Services
require __DIR__ . $ds . 'services.php';

// Router
$router = $app->router();

// Routes
require __DIR__ . $ds . 'routes.php';

// Start
$app->start();

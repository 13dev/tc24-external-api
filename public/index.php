<?php
if (PHP_SAPI === 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();


// Set up global dependencies
require __DIR__ . '/../app/dependencies.php';

// Register global middleware's
require __DIR__ . '/../app/middleware.php';

// Register global routes
require __DIR__ . '/../app/routes.php';

// Run app
$app->run();
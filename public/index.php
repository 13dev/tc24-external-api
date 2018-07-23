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
$settings = require __DIR__ . '/../src/App/settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();


// Set up global dependencies
require __DIR__ . '/../src/App/dependencies.php';

// Register global middleware's
require __DIR__ . '/../src/App/middleware.php';

// Register global routes
require __DIR__ . '/../src/App/routes.php';

// Register the modules dependencies, routes etc ...
// Iteract All Modules
foreach (glob(__DIR__ . '/../src/Modules/*' , GLOB_ONLYDIR) as $module) {
    // Get files of modules
    foreach (glob($module . '/*.php') as $filename) {
        require_once $filename;
    }
}


// Run app
try {
    $app->run();
} catch (\Slim\Exception\MethodNotAllowedException $e) {

} catch (\Slim\Exception\NotFoundException $e) {

}

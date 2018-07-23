<?php

use Slim\Http\Request;
use Slim\Http\Response;

$prefixApi = $container->get('settings')['prefixApi'];

// Routes API
$app->group($prefixApi, function() use($prefixApi, $container) {
    $container->get('logger')->info("Slim-Skeleton '{$prefixApi}' route");

    $this->get('/', function(Request $request, Response $response, $args) {
        return 'Workings';
    });
});


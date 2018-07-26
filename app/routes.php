<?php

use Slim\Http\Request;
use Slim\Http\Response;

$prefixApi = $container->get('settings')['prefixApi'];

// Routes API
$app->group($prefixApi, function() use($prefixApi, $container) {
    // Register the tracking route
    $this->post('/tracking', 'App\Action\TrackingAction:postTracking');
    $this->get('/tracking', 'App\Action\TrackingAction:getTracking');
});


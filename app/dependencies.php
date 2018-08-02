<?php

use App\Action\CustomerAction;
use App\Action\TrackingAction;
use App\FJson;
use App\Resource\CustomerResource;
use App\Resource\TrackerResource;

/**
 * Inject Monolog
 * @param $container - container
 * @return \Monolog\Logger
 */
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

/**
 * Include validator
 * @return \Awurth\SlimValidation\Validator
 */
$container['validator'] = function () {
    return new Awurth\SlimValidation\Validator(false);
};

/**
 * Inject Doctrine
 * @param $container - Container
 * @return \Doctrine\ORM\EntityManager
 */
$container['em'] = function ($container) {
    $settings = $container->get('settings')['doctrine'];
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['meta']['entity_path'],
        $settings['meta']['auto_generate_proxies'],
        $settings['meta']['proxy_dir'],
        $settings['meta']['cache'],
        false
    );
    return \Doctrine\ORM\EntityManager::create($settings['connection'], $config);
};

/**
 * Inject a customer response to format json.
 * @param $container
 * @return FJson
 */
$container['fjson'] = function($container) {
    return new FJson($container->get('response'));
};

/**
 * Generic Exceptions to JSON
 * @param $container
 * @return Closure
 */
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $statusCode = $exception->getCode() ? $exception->getCode() : 500;
        return $container['response']->withStatus($statusCode)
            ->withHeader('Content-Type', 'Application/json')
            ->withJson(['message' => $exception->getMessage()], $statusCode);
    };
};

/**
 * Exceptions 405 - Not Allowed to Json
 * @param $container
 * @return Closure
 */
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        return $container['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'Application/json')
            ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
            ->withJson(['message' => 'Method not Allowed; Method must be one of: ' . implode(', ', $methods)], 405);
    };
};

/**
 * Exceptions 404 - Not Found to JSON
 * @param $container
 * @return Closure
 */
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'Application/json')
            ->withJson(['message' => 'Page not found']);
    };
};

/**
 * Inject the Tracking action (main of this project)
 * @param $container
 * @return TrackingAction
 */
$container[TrackingAction::class] = function ($container) {
    return new TrackingAction(
        $container,
        new TrackerResource($container->get('em'), $container->get('logger')),
        new CustomerResource($container->get('em'), $container->get('logger'))
    );
};

/**
 * Register the customer action
 * @param $container
 * @return CustomerAction
 */
$container[CustomerAction::class] = function ($container) {
    return new CustomerAction(
        $container,
        new TrackerResource($container->get('em'), $container->get('logger')),
        new CustomerResource($container->get('em'), $container->get('logger'))
    );
};

<?php

/**
 * Inject Monolog
 * @param $c - container
 * @return \Monolog\Logger
 */
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
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
 * @param $c - Container
 * @return \Doctrine\ORM\EntityManager
 */
$container['em'] = function ($c) {
    $settings = $c->get('settings')['doctrine'];
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
 * Generic Exceptions to JSON
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
 */
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        return $container['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'Application/json')
            ->withHeader('Access-Control-Allow-Methods', implode(",", $methods))
            ->withJson(['message' => 'Method not Allowed; Method must be one of: ' . implode(', ', $methods)], 405);
    };
};

/**
 * Exceptions 404 - Not Found to JSON
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
 * Register Multiple actions, that only need the container..
 */
//new \App\MultipleRegistor($container , [
//    'App\Action\TrackingAction',
//]);

$container['App\Action\TrackingAction'] = function ($container) {
    $trackingResource = new \App\Resource\TrackerResource($container->get('em'), $container->get('logger'));
    $customerResource = new \App\Resource\CustomerResource($container->get('em'), $container->get('logger'));
    return new App\Action\TrackingAction($container, $trackingResource, $customerResource);
};

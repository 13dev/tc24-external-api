<?php
// Cli configuration
// Doctrine
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$settings = (require __DIR__ . '/../src/App/settings.php')['settings']['doctrine'];

$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
    $settings['meta']['entity_path'],
    $settings['meta']['auto_generate_proxies'],
    $settings['meta']['proxy_dir'],
    $settings['meta']['cache'],
    false
);

try {
    $em = \Doctrine\ORM\EntityManager::create($settings['connection'], $config);
} catch (\Doctrine\ORM\ORMException $e) {
    die('Error creating Entity Manager, please try again...');
}

return ConsoleRunner::createHelperSet($em);
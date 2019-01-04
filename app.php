#!/usr/bin/php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

try
{
    $containerBuilder = new ContainerBuilder();
    $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
    $loader->load('config/services.yml', 'yaml');

    $app = new Application();
    /** @var \Symfony\Component\Console\Command\Command $command */
    $command = $containerBuilder->get('command.download');
    $app->add($command);

    $app->run();
}
catch (Exception $e)
{
    echo $e->getMessage();
    die();
}

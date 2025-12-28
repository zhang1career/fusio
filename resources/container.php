<?php

use Doctrine\DBAL\Connection;
use Fusio\Engine\Adapter\ServiceBuilder;
use PSX\Framework\Dependency\Configurator;
use PSX\Framework\Messenger\Transport\DoctrineTransportFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services = Configurator::services($services);

    // Register Redis transport factory for Symfony Messenger
    // Note: Requires Redis 5.0+ for Streams support
    $services->set('messenger.transport.redis.factory', RedisTransportFactory::class)
        ->tag('psx.messenger_transport_factory');

    /*
    $services->load('App\\Action\\', __DIR__ . '/../src/Action');
    $services->load('App\\Connection\\', __DIR__ . '/../src/Connection');

    $services->load('App\\Service\\', __DIR__ . '/../src/Service')
        ->public();

    $services->load('App\\Table\\', __DIR__ . '/../src/Table')
        ->exclude('Generated')
        ->public();

    $services->load('App\\View\\', __DIR__ . '/../src/View');
    */
};

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoConnectivityConnectionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('install.yml');
        $loader->load('services.yml');
        $loader->load('validators.yml');
        $loader->load('wrong_credentials_connection.yml');

        $loader->load('cli.yml');
        $loader->load('handlers.yml');
        $loader->load('queries.yml');
        $loader->load('repositories.yml');
        $loader->load('controllers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('serializers.yml');
        $loader->load('documentation.yml');
        $loader->load('message_handler.yml');
    }
}

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
        $container->setParameter('connectivity.marketplace_fixtures_directory', __DIR__ . '/../Resources/fixtures/');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('Apps/commands.yml');
        $loader->load('Apps/controllers.yml');
        $loader->load('Apps/handlers.yml');
        $loader->load('Apps/install.yml');
        $loader->load('Apps/normalizers.yml');
        $loader->load('Apps/oauth.yml');
        $loader->load('Apps/queries.yml');
        $loader->load('Apps/services.yml');
        $loader->load('Apps/validators.yml');
        $loader->load('cli.yml');
        $loader->load('controllers.yml');
        $loader->load('event_normalizers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('feature_flag.yml');
        $loader->load('handlers.yml');
        $loader->load('install.yml');
        $loader->load('marketplace.yml');
        $loader->load('message_handler.yml');
        $loader->load('queries.yml');
        $loader->load('repositories.yml');
        $loader->load('serializers.yml');
        $loader->load('services.yml');
        $loader->load('validators.yml');
        $loader->load('webhook.yml');
        $loader->load('wrong_credentials_connection.yml');
    }
}

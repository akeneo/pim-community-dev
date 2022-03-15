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

        $loader->load('Audit/commands.yml');
        $loader->load('Audit/controllers.yml');
        $loader->load('Audit/handlers.yml');
        $loader->load('Audit/install.yml');
        $loader->load('Audit/queries.yml');

        $loader->load('ErrorManagement/commands.yml');
        $loader->load('ErrorManagement/controllers.yml');
        $loader->load('ErrorManagement/event_subscribers.yml');
        $loader->load('ErrorManagement/handlers.yml');
        $loader->load('ErrorManagement/queries.yml');
        $loader->load('ErrorManagement/services.yml');

        $loader->load('Marketplace/controllers.yml');
        $loader->load('Marketplace/handlers.yml');
        $loader->load('Marketplace/install.yml');
        $loader->load('Marketplace/queries.yml');
        $loader->load('Marketplace/services.yml');

        $loader->load('Webhook/commands.yml');
        $loader->load('Webhook/controllers.yml');
        $loader->load('Webhook/event_normalizers.yml');
        $loader->load('Webhook/event_subscribers.yml');
        $loader->load('Webhook/handlers.yml');
        $loader->load('Webhook/install.yml');
        $loader->load('Webhook/message_handlers.yml');
        $loader->load('Webhook/queries.yml');
        $loader->load('Webhook/services.yml');
        $loader->load('Webhook/validators.yml');

        $loader->load('cli.yml');
        $loader->load('controllers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('feature_flag.yml');
        $loader->load('handlers.yml');
        $loader->load('install.yml');
        $loader->load('queries.yml');
        $loader->load('repositories.yml');
        $loader->load('serializers.yml');
        $loader->load('services.yml');
        $loader->load('validators.yml');
        $loader->load('wrong_credentials_connection.yml');
    }
}

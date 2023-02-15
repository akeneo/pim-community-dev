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
        $loader->load('Apps/event_subscribers.yml');
        $loader->load('Apps/feature_flag.yml');
        $loader->load('Apps/handlers.yml');
        $loader->load('Apps/install.yml');
        $loader->load('Apps/normalizers.yml');
        $loader->load('Apps/oauth.yml');
        $loader->load('Apps/persistence.yml');
        $loader->load('Apps/services.yml');
        $loader->load('Apps/validators.yml');

        $loader->load('Audit/commands.yml');
        $loader->load('Audit/controllers.yml');
        $loader->load('Audit/event_subscribers.yml');
        $loader->load('Audit/handlers.yml');
        $loader->load('Audit/install.yml');
        $loader->load('Audit/persistence.yml');
        $loader->load('Audit/services.yml');
        $loader->load('Audit/jobs.yml');

        $loader->load('Connections/command.yml');
        $loader->load('Connections/controllers.yml');
        $loader->load('Connections/event_subscribers.yml');
        $loader->load('Connections/install.yml');
        $loader->load('Connections/jobs.yml');
        $loader->load('Connections/persistence.yml');
        $loader->load('Connections/services.yml');

        $loader->load('ErrorManagement/commands.yml');
        $loader->load('ErrorManagement/controllers.yml');
        $loader->load('ErrorManagement/event_subscribers.yml');
        $loader->load('ErrorManagement/handlers.yml');
        $loader->load('ErrorManagement/jobs.yml');
        $loader->load('ErrorManagement/persistence.yml');
        $loader->load('ErrorManagement/services.yml');

        $loader->load('Marketplace/controllers.yml');
        $loader->load('Marketplace/feature_flag.yml');
        $loader->load('Marketplace/install.yml');
        $loader->load('Marketplace/persistence.yml');
        $loader->load('Marketplace/services.yml');

        $loader->load('CustomApps/controllers.yml');
        $loader->load('CustomApps/handlers.yml');
        $loader->load('CustomApps/persistence.yml');
        $loader->load('CustomApps/services.yml');
        $loader->load('CustomApps/validators.yml');

        $loader->load('Settings/controllers.yml');
        $loader->load('Settings/handlers.yml');
        $loader->load('Settings/persistence.yml');
        $loader->load('Settings/services.yml');
        $loader->load('Settings/validators.yml');

        $loader->load('Webhook/commands.yml');
        $loader->load('Webhook/controllers.yml');
        $loader->load('Webhook/event_normalizers.yml');
        $loader->load('Webhook/event_subscribers.yml');
        $loader->load('Webhook/handlers.yml');
        $loader->load('Webhook/install.yml');
        $loader->load('Webhook/message_handlers.yml');
        $loader->load('Webhook/persistence.yml');
        $loader->load('Webhook/services.yml');
        $loader->load('Webhook/validators.yml');
        $loader->load('Webhook/jobs.yml');

        $loader->load('services.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('Webhook/services_test.yml');
        }
    }
}

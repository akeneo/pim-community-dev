<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AkeneoFreeTrialExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('external_javascript_dependencies.yml');
        $loader->load('feature_flags.yml');
        $loader->load('controllers.yml');
        $loader->load('subscribers.yml');
        $loader->load('akeneo_connect.yml');
        $loader->load('mysql.yml');
        $loader->load('services.yml');
    }
}

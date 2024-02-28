<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AkeneoJobExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('handlers.yml');
        $loader->load('controllers.yml');
        $loader->load('hydrators.yml');
        $loader->load('queries.yml');
        $loader->load('services.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('test/services.yml');
        }
    }
}

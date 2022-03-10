<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AkeneoOnboarderSerenityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config/');
        $loader = new DirectoryLoader($container, $fileLocator);
        $loader->setResolver(new LoaderResolver([
            new YamlFileLoader($container, $fileLocator),
            $loader,
        ]));
        $loader->load('services');
    }
}

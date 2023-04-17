<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AkeneoPimEnrichmentProductExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     *
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('anti_corruption_layer.yml');
        $loader->load('appliers.yml');
        $loader->load('factories.yml');
        $loader->load('handlers.yml');
        $loader->load('message_bus.yml');
        $loader->load('queries.yml');
        $loader->load('query_builders.yml');
        $loader->load('services.yml');
        $loader->load('subscribers.yml');
        $loader->load('validators.yml');
    }
}

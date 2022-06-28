<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AkeneoPimEnterpriseProductEnrichmentExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('product_values.yml');
        $loader->load('duplicate_product.yml');
        $loader->load('webhook.yml');
    }
}

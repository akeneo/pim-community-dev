<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoPimEnrichmentAssetManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('analytics.yml');
        $loader->load('array_converter.yml');
        $loader->load('datagrid/attribute_types.yml');
        $loader->load('datagrid/formatters.yml');
        $loader->load('datagrid/filters.yml');
        $loader->load('datagrid/query.yml');
        $loader->load('datagrid/normalizer.yml');
        $loader->load('jobs.yml');
        $loader->load('parameters.yml');
        $loader->load('processors.yml');
        $loader->load('product_value.yml');
        $loader->load('services.yml');
        $loader->load('writers.yml');
    }
}

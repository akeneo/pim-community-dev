<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Akeneo Asset Family extension
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AkeneoAssetManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('attribute_factories.yml');
        $loader->load('attribute_command_factories.yml');
        $loader->load('asset_command_factories.yml');
        $loader->load('controllers.yml');
        $loader->load('compute_transformations.yml');
        $loader->load('files.yml');
        $loader->load('filters.yml');
        $loader->load('handlers.yml');
        $loader->load('jobs.yml');
        $loader->load('parameters.yml');
        $loader->load('persistence.yml');
        $loader->load('preview_generators.yml');
        $loader->load('public_api/analytics.yml');
        $loader->load('public_api/enrichment.yml');
        $loader->load('public_api/onboarder.yml');
        $loader->load('rule_templates.yml');
        $loader->load('serializer.yml');
        $loader->load('services.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('search/services.yml');

        $loader->load('connector/json_schema_validators.yml');
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AkeneoTailoredExportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('format_appliers.yml');
        $loader->load('hydrators.yml');
        $loader->load('jobs.yml');
        $loader->load('operation_appliers.yml');
        $loader->load('queries.yml');
        $loader->load('selection_appliers.yml');
        $loader->load('services.yml');
        $loader->load('validations.yml');

        $this->configureAssetManagerRelatedServices($container);
        $this->configureReferenceEntityRelatedServices($container);
    }

    /**
     * Enable or disable services related to Asset Manager based
     * on the presence of the Asset Manager bundle.
     *
     * TODO: TIP-1569: remove this condition once GE is merged into Serenity codebase
     */
    private function configureAssetManagerRelatedServices(ContainerBuilder $container): void
    {
        /** @var array $bundles */
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['AkeneoAssetManagerBundle'])) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/asset_manager'));
            $loader->load('services.yml');
            $loader->load('queries.yml');
            $loader->load('validations.yml');
            $loader->load('selection_appliers.yml');
        }
    }

    /**
     * Enable or disable services related to Asset Manager based
     * on the presence of the Reference Entity bundle.
     */
    private function configureReferenceEntityRelatedServices(ContainerBuilder $container): void
    {
        /** @var array $bundles */
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['AkeneoReferenceEntityBundle'])) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/reference_entity'));
            $loader->load('controllers.yml');
            $loader->load('queries.yml');
            $loader->load('selection_appliers.yml');
            $loader->load('validations.yml');
        }
    }
}

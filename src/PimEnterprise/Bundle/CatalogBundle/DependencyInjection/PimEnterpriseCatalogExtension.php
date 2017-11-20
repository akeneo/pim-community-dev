<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Catalog extension
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimEnterpriseCatalogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('comparators.yml');
        $loader->load('completeness.yml');
        $loader->load('completeness_checkers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('filters.yml');
        $loader->load('managers.yml');
        $loader->load('repositories.yml');
        $loader->load('security/appliers.yml');
        $loader->load('security/factories.yml');
        $loader->load('security/filters.yml');
        $loader->load('security/mergers.yml');
        $loader->load('security/query_builders.yml');
        $loader->load('security/updaters.yml');
        $loader->load('serializers.yml');
        $loader->load('serializers_standard.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('versions.yml');
    }
}

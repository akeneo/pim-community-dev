<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * PIM Enterprise Rule Engine extension
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class PimEnterpriseCatalogRuleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('connector_steps.yml');
        $loader->load('controllers.yml');
        $loader->load('engine.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('runners.yml');
        $loader->load('readers.yml');
        $loader->load('managers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('repositories.yml');
        $loader->load('runners.yml');
        $loader->load('serializers.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('datagrid_extensions.yml');
        $loader->load('datagrid_filters.yml');
    }
}

<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * PimEnterprise\Bundle\WorkflowBundle\DependencyInjection
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PimEnterpriseWorkflowExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('entities.yml');
        $loader->load('product_draft.yml');
        $loader->load('presenters.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('managers.yml');
        $loader->load('savers.yml');
        $loader->load('publishers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('helpers.yml');
        $loader->load('comparators.yml');
        $loader->load('factories.yml');
        $loader->load('controllers.yml');
        $loader->load('query_builders.yml');
        $loader->load('twig.yml');
        $loader->load('builder.yml');
        $loader->load('appliers.yml');
        $loader->load('removers.yml');

        $storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));
    }
}

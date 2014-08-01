<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * PimEnterprise\Bundle\WorkflowBundle\DependencyInjection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        $loader->load('form_types.yml');
        $loader->load('product_draft.yml');
        $loader->load('presenters.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('managers.yml');
        $loader->load('persisters.yml');
        $loader->load('publishers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('helpers.yml');
        $loader->load('comparators.yml');
        $loader->load('factories.yml');
        $loader->load('controllers.yml');
        $loader->load('repositories.yml');
        $loader->load('twig.yml');

        $storageDriver = $container->getParameter('pim_catalog.storage_driver');
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));
    }
}

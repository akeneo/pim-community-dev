<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PimEnterpriseActivityManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('project.yml');
        $loader->load('jobs.yml');
        $loader->load('services.yml');
        $loader->load('removers.yml');

        $storageDriver = $containerBuilder->getParameter('pim_catalog_product_storage_driver');
        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $storageDriver) {
            $containerBuilder->removeDefinition('pimee_activity_manager.calculation_step.link_product_category');
        } else {
            $preProcessingTables = $containerBuilder->getParameter('pimee_activity_manager.pre_processing.tables');
            $preProcessingTables['pim_catalog.entity.product#categories'] = 'pimee_activity_manager_product_category';
            $preProcessingTables['pimee_activity_manager.product_category'] = 'pimee_activity_manager_product_category';
            $containerBuilder->setParameter('pimee_activity_manager.pre_processing.tables', $preProcessingTables);
        }
    }
}

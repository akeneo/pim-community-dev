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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PimEnterpriseActivityManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('jobs.yml');
        $loader->load('project.yml');
        $loader->load('services.yml');
        $this->loadStorageDriver($containerBuilder);
    }

    /**
     * Load the mapping for product and product storage
     *
     * @param ContainerBuilder $containerBuilder
     */
    protected function loadStorageDriver(ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));
        $storageDriver = $containerBuilder->getParameter('pim_catalog_product_storage_driver');
        $storageConfig = sprintf('storage_driver/%s.yml', $storageDriver);
        if (file_exists(__DIR__ . '/../Resources/config/' . $storageConfig)) {
            $loader->load($storageConfig);
        }
    }
}

<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Import export bundle extension
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('grid.yml');
        $loader->load('forms.yml');
        $loader->load('form_types.yml');
        $loader->load('job_labels.yml');
        $loader->load('job_parameters.yml');
        $loader->load('controllers.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('managers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('factory.yml');
        $loader->load('twig.yml');
        $loader->load('services.yml');
        $loader->load('view_elements.yml');
        $loader->load('view_elements/job_profile.yml');

        $this->loadStorageDriver($container);
    }


    /**
     * Load the mapping for product and product storage
     *
     * @param ContainerBuilder $container
     */
    protected function loadStorageDriver(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
        $storageConfig = sprintf('storage_driver/%s.yml', $storageDriver);
        if (file_exists(__DIR__ . '/../Resources/config/' . $storageConfig)) {
            $loader->load($storageConfig);
        }
    }
}

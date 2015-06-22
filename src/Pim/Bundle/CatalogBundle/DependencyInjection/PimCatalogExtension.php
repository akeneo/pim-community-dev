<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('attribute_types.yml');
        $loader->load('builders.yml');
        $loader->load('comparators.yml');
        $loader->load('context.yml');
        $loader->load('doctrine.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('filters.yml');
        $loader->load('helpers.yml');
        $loader->load('managers.yml');
        $loader->load('models.yml');
        $loader->load('query_builders.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('resolvers.yml');
        $loader->load('savers.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');

        $this->loadValidationFiles($container);
        $this->loadStorageDriver($container);
    }

    /**
     * Loads the validation files from all bundles
     *
     * @param ContainerBuilder $container
     */
    protected function loadValidationFiles(ContainerBuilder $container)
    {
        $dirs = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $dir = dirname($reflection->getFileName()) . '/Resources/config/validation';
            if (is_dir($dir)) {
                $dirs[] = $dir;
            }
        }
        $finder = new Finder();
        $mappingFiles = array();
        foreach ($finder->files()->in($dirs) as $file) {
            $mappingFiles[$file->getBasename('.yml')] = $file->getRealPath();
        }
        $mappingFiles = array_merge(
            $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files'),
            array_values($mappingFiles)
        );
        $container->setParameter(
            'validator.mapping.loader.yaml_files_loader.mapping_files',
            $mappingFiles
        );
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

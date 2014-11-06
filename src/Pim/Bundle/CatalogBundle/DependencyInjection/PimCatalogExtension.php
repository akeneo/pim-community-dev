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
    /** @staticvar string */
    const DOCTRINE_ORM = 'doctrine/orm';

    /** @staticvar string */
    const DOCTRINE_MONGODB_ODM = 'doctrine/mongodb-odm';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine.yml');
        $loader->load('context.yml');
        $loader->load('validators.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('managers.yml');
        $loader->load('persisters.yml');
        $loader->load('builders.yml');
        $loader->load('helpers.yml');
        $loader->load('attribute_types.yml');
        $loader->load('factories.yml');
        $loader->load('entities.yml');
        $loader->load('repositories.yml');
        $loader->load('query_builders.yml');
        $loader->load('updaters.yml');

        $this->loadStorageDriver($config, $container);
        $this->loadValidationFiles($container);
    }

    /**
     * Loads the validation files
     *
     * @param ContainerBuilder $container
     */
    protected function loadValidationFiles(ContainerBuilder $container)
    {
        // load validation files
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
        $container->setParameter(
            'validator.mapping.loader.yaml_files_loader.mapping_files',
            array_merge(
                $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files'),
                array_values($mappingFiles)
            )
        );
    }

    /**
     * Provides the supported driver for product storage
     * @return string[]
     */
    protected function getSupportedStorageDrivers()
    {
        return array(self::DOCTRINE_ORM, self::DOCTRINE_MONGODB_ODM);
    }

    /**
     * Load the mapping for product and product storage
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadStorageDriver(array $config, ContainerBuilder $container)
    {
        $storageDriver = $config['storage_driver'];

        if (!in_array($storageDriver, $this->getSupportedStorageDrivers())) {
            throw new \RuntimeException("The storage driver $storageDriver is not a supported drivers");
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));

        $container->setParameter($this->getAlias().'.storage_driver', $storageDriver);
        // Parameter defining if the mapping driver must be enabled or not
        $container->setParameter($this->getAlias().'.storage_driver.'.$storageDriver, true);
    }
}

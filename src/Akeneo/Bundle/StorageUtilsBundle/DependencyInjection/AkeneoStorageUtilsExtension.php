<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoStorageUtilsExtension extends Extension
{
    /** @staticvar string */
    const DOCTRINE_ORM = 'doctrine/orm';

    /** @var string */
    protected static $storageDriver;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        self::$storageDriver = $config['storage_driver'];

        $container->setParameter($this->getAlias() . '.mapping_overrides', $config['mapping_overrides']);

        $container->setParameter($this->getAlias() . '.storage_driver', self::$storageDriver);
        // Parameter defining if the mapping driver must be enabled or not
        $container->setParameter($this->getAlias() . '.storage_driver.' . self::$storageDriver, true);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('doctrine.yml');
        $loader->load('factories.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');

        $this->loadStorageDriver($container, __DIR__);
    }

    /**
     * Provides the supported driver for application storage
     *
     * @return string[]
     */
    public static function getSupportedStorageDrivers()
    {
        return [self::DOCTRINE_ORM];
    }

    /**
     * Load the mapping for application storage
     *
     * TODO: rename this method
     *
     * @param ContainerBuilder $container
     * @param string           $path
     */
    protected function loadStorageDriver(ContainerBuilder $container, $path)
    {
        if (!in_array(self::$storageDriver, $this->getSupportedStorageDrivers())) {
            throw new \RuntimeException(
                sprintf(
                    'The storage driver "%s" is not supported.',
                    self::$storageDriver
                )
            );
        }

        $loader = new YamlFileLoader($container, new FileLocator($path . '/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', self::$storageDriver));
    }
}

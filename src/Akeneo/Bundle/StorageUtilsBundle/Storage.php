<?php

namespace Akeneo\Bundle\StorageUtilsBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Storage
{
    /** @staticvar string */
    const STORAGE_DOCTRINE_ORM = 'doctrine/orm';

    /** @staticvar string */
    const STORAGE_DOCTRINE_MONGODB_ODM = 'doctrine/mongodb-odm';

    /** @var string */
    private static $storageDriver;

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function getStorageDriver()
    {
        if (null === self::$storageDriver) {
            throw new \RuntimeException('The storage driver has not been set.');
        }

        return self::$storageDriver;
    }

    /**
     * @param string $driver
     *
     * @throws \RuntimeException
     */
    public static function setStorageDriver($driver)
    {
        if (!in_array($driver, Storage::getSupportedStorageDrivers())) {
            throw new \RuntimeException(sprintf('The storage driver "%s" is not supported.', $driver));
        }

        self::$storageDriver = $driver;
    }

    /**
     * Provides the supported drivers for application storage
     *
     * @return string[]
     */
    public static function getSupportedStorageDrivers()
    {
        return array(self::STORAGE_DOCTRINE_ORM, self::STORAGE_DOCTRINE_MONGODB_ODM);
    }

    public static function loadStorageConfigFiles(ContainerBuilder $container, $path)
    {
        $loader = new YamlFileLoader($container, new FileLocator($path . '/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', Storage::getStorageDriver()));
    }
}

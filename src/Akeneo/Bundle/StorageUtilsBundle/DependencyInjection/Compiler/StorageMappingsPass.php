<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\Storage;
use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass;

class StorageMappingsPass
{
    const DOCTRINE_ORM_MAPPINGS_PASS = '\Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
    const DOCTRINE_MONGODB_MAPPINGS_PASS = '\Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass';

    /**
     * @param string $driver
     * @param array  $mappings
     * @param bool   $checkContainer
     *
     * @return RegisterMappingsPass
     */
    public static function getMappingsPass($driver, array $mappings, $checkContainer = false)
    {
        if (!in_array($driver, Storage::getSupportedStorageDrivers())) {
            throw new \RuntimeException(sprintf('The storage driver "%s" is not supported.', $driver));
        }

        $managerParameters = ['doctrine.orm.entity_manager'];
        $mappingsPassClass = self::DOCTRINE_ORM_MAPPINGS_PASS;

        if (Storage::STORAGE_DOCTRINE_MONGODB_ODM === $driver) {
            $managerParameters = ['doctrine.odm.mongodb.document_manager'];
            $mappingsPassClass = self::DOCTRINE_MONGODB_MAPPINGS_PASS;
        }

        if (!class_exists($mappingsPassClass)) {
            throw new \RuntimeException(sprintf('The mapping class "%s" does not exist.', $mappingsPassClass));
        }

        $containerParameter = $checkContainer === true ?
            sprintf('akeneo_storage_utils.storage_driver.%s', $driver) : false;

        return $mappingsPassClass::createYamlMappingDriver(
            $mappings,
            $managerParameters,
            $containerParameter
        );
    }
}

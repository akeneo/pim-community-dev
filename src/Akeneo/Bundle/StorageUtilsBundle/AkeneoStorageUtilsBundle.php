<?php

namespace Akeneo\Bundle\StorageUtilsBundle;

//TODO: should be trashed
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Akeneo storage utils bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoStorageUtilsBundle extends Bundle
{
    /** @staticvar string */
    const DOCTRINE_MONGODB = '\Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass';

    /** @staticvar string */
    const ODM_ENTITIES_TYPE = 'entities';

    /** @staticvar string */
    const ODM_ENTITY_TYPE = 'entity';

    /**
     * Register custom doctrine types
     */
    public function __construct()
    {
        if (class_exists('\Doctrine\ODM\MongoDB\Types\Type')) {
            \Doctrine\ODM\MongoDB\Types\Type::registerType(
                self::ODM_ENTITIES_TYPE,
                'Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entities'
            );

            \Doctrine\ODM\MongoDB\Types\Type::registerType(
                self::ODM_ENTITY_TYPE,
                'Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entity'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $interfaces = $this->getModelInterfaces();
        if (!empty($interfaces)) {
            $container->addCompilerPass(
                new ResolveDoctrineTargetModelPass($interfaces)
            );
        }

        $doctrineMapping = $this->getDoctrineMappingDriverConfig();
        if (!empty($doctrineMapping)) {
            $this->registerDoctrineMappingDriver($container, $doctrineMapping);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $mappings
     */
    protected function registerDoctrineMappingDriver(ContainerBuilder $container, array $mappings)
    {
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                array('doctrine.orm.entity_manager'),
                'akeneo_storage_utils.storage_driver.doctrine/orm'
            )
        );

        if (class_exists(self::DOCTRINE_MONGODB)) {
            $mongoDBClass = self::DOCTRINE_MONGODB;
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $mappings,
                    array('doctrine.odm.mongodb.document_manager'),
                    'akeneo_storage_utils.storage_driver.doctrine/mongodb-odm'
                )
            );
        }
    }

    /**
     * Return the absolute path where are stored the doctrine mapping.
     *
     * @return string
     */
    protected function getDoctrineMappingDriverDirectory($directory)
    {
        return sprintf(
            '%s/Resources/config/%s',
            $this->getPath(),
            $directory
        );
    }

    /**
     * Return the doctrine driver configuration (Directory - Namespace)
     *
     * @return array
     */
    protected function getDoctrineMappingDriverConfig()
    {
        return [];
    }

    /**
     * Target entities resolver configuration (Interface - Model).
     *
     * @return array
     */
    protected function getModelInterfaces()
    {
        return [];
    }
}

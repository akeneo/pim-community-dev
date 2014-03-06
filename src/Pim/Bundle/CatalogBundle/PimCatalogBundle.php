<?php

namespace Pim\Bundle\CatalogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends Bundle
{
    /** @staticvar string */
    const DOCTRINE_MONGODB = '\Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass';

    /** @staticvar string */
    const VERSION = '1.1.0-DEV';

    /** @staticvar string */
    const VERSION_CODENAME = '';

    /** @staticvar string */
    const MAJOR_VERSION = '1';

    /** @staticvar string */
    const MINOR_VERSION = '1';

    /** @staticvar string */
    const PATCH_VERSION = '0';

    /** @staticvar string */
    const EXTRA_VERSION = '';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\ResolveDoctrineOrmTargetEntitiesPass())
            ->addCompilerPass(new Compiler\RegisterAttributeConstraintGuessersPass());

        $productMappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\CatalogBundle\Model'
        );

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                array('doctrine.orm.entity_manager'),
                'pim_catalog.storage_driver.doctrine/orm'
            )
        );

        if (class_exists(self::DOCTRINE_MONGODB)) {
            $mongoDBClass = self::DOCTRINE_MONGODB;
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $productMappings,
                    array('doctrine.odm.mongodb.document_manager'),
                    'pim_catalog.storage_driver.doctrine/mongodb-odm'
                )
            );
        }
    }
}

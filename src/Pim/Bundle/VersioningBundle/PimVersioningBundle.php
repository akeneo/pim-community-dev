<?php

namespace Pim\Bundle\VersioningBundle;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\CatalogBundle\PimCatalogBundle;
use Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Versioning Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimVersioningBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterUpdateGuessersPass())
            ->addCompilerPass(new SerializerPass('pim_versioning.serializer'));

        $versionMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\VersioningBundle\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $versionMappings,
                ['doctrine.orm.entity_manager'],
                'pim_catalog.storage_driver.doctrine/orm'
            )
        );

        if (class_exists(PimCatalogBundle::DOCTRINE_MONGODB)) {
            $mongoDBClass = PimCatalogBundle::DOCTRINE_MONGODB;
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $versionMappings,
                    ['doctrine.odm.mongodb.document_manager'],
                    'pim_catalog.storage_driver.doctrine/mongodb-odm'
                )
            );
        }
    }
}

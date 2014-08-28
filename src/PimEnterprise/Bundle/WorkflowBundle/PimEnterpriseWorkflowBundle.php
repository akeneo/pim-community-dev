<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

/**
 * PIM Enterprise Workflow Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseWorkflowBundle extends Bundle
{
    /** @staticvar string */
    const DOCTRINE_MONGODB = '\Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler\ResolveDoctrineOrmTargetEntitiesPass());

        $mappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'PimEnterprise\Bundle\WorkflowBundle\Model'
        );

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                array('doctrine.orm.entity_manager'),
                'pim_catalog.storage_driver.doctrine/orm'
            )
        );

        if (class_exists(self::DOCTRINE_MONGODB)) {
            $mongoDBClass = self::DOCTRINE_MONGODB;
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $mappings,
                    array('doctrine.odm.mongodb.document_manager'),
                    'pim_catalog.storage_driver.doctrine/mongodb-odm'
                )
            );
        }

        $container
            ->addCompilerPass(new Compiler\RegisterProductDraftPresentersPass())
            ->addCompilerPass(new Compiler\RegisterProductValuePresentersPass())
            ->addCompilerPass(new Compiler\RegisterComparatorsPass())
            ->addCompilerPass(new Compiler\RegisterPublishersPass());
    }
}

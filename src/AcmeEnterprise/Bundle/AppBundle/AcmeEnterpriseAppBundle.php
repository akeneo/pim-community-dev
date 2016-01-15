<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeEnterprise\Bundle\AppBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Acme Enterprise application bundle
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AcmeEnterpriseAppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $productMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'AcmeEnterprise\Bundle\AppBundle\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                'akeneo_storage_utils.storage_driver.doctrine/orm'
            )
        );

        $mongoDBClass = AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM;
        if ($mongoDBClass === $this->container->get('pim_catalog_product_storage_driver')) {
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $productMappings,
                    ['doctrine.odm.mongodb.document_manager'],
                    'akeneo_storage_utils.storage_driver.doctrine/mongodb-odm'
                )
            );
        }
    }
}

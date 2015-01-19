<?php

namespace Pim\Bundle\CatalogBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\StorageMappingsPass;
use Akeneo\Bundle\StorageUtilsBundle\Storage;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterQueryGeneratorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\CatalogBundle\Model'
        );

        $ormMappingsPass = StorageMappingsPass::getMappingsPass(Storage::STORAGE_DOCTRINE_ORM, $mappings, true);
        $mongoMappingsPass = StorageMappingsPass::getMappingsPass(Storage::STORAGE_DOCTRINE_MONGODB_ODM, $mappings, true);

        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new ResolveDoctrineTargetRepositoryPass('pim_repository'))
            ->addCompilerPass(new RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new RegisterAttributeTypePass())
            ->addCompilerPass(new RegisterQueryGeneratorsPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass())
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass())
            ->addCompilerPass($ormMappingsPass)
            ->addCompilerPass($mongoMappingsPass)
        ;
    }
}

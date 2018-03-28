<?php

namespace Pim\Bundle\CatalogBundle;

use Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\Localization\RegisterLocalizersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\Localization\RegisterPresentersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterComparatorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterCompleteCheckerPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterQueryGeneratorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterValueFactoryPass;
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
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new ResolveDoctrineTargetRepositoryPass('pim_repository'))
            ->addCompilerPass(new RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new RegisterAttributeTypePass())
            ->addCompilerPass(new RegisterValueFactoryPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass('product'))
            ->addCompilerPass(new RegisterProductQueryFilterPass('product_model'))
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass())
            ->addCompilerPass(new RegisterFilterPass())
            ->addCompilerPass(new RegisterComparatorsPass())
            ->addCompilerPass(new RegisterCompleteCheckerPass())
            ->addCompilerPass(new RegisterLocalizersPass())
            ->addCompilerPass(new RegisterPresentersPass())
            ->addCompilerPass(new RegisterSerializerPass('pim_serializer'));

        $productMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Component\Catalog\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}

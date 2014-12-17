<?php

namespace Pim\Bundle\CatalogBundle;

use Akeneo\Bundle\DoctrineExtensionsBundle\AkeneoDoctrineExtensionsBundle;
use Akeneo\Bundle\DoctrineExtensionsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoriesPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterQueryGeneratorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends AkeneoDoctrineExtensionsBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelsPass())
            ->addCompilerPass(new ResolveDoctrineTargetRepositoriesPass('pim_repository'))
            ->addCompilerPass(new RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new RegisterAttributeTypePass())
            ->addCompilerPass(new RegisterQueryGeneratorsPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass())
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass());
        ;

        $productMappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\CatalogBundle\Model'
        );

        $this->registerDoctrineMappingDriver($container, $productMappings);
    }
}

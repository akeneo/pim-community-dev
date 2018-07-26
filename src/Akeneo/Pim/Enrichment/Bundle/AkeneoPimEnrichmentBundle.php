<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterLocalizersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterPresentersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterComparatorsPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterCompleteCheckerPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterFilterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterRendererPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterValueFactoryPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimEnrichmentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
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
            ->addCompilerPass(new RegisterSerializerPass('pim_serializer'))
            ->addCompilerPass(new RegisterRendererPass());
        ;

        $mappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/Product') => 'Akeneo\Pim\Enrichment\Component\Product\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/Category') => 'Akeneo\Pim\Enrichment\Component\Category\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}

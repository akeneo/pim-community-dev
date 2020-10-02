<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\ConfigureAxisValueLabelsNormalizerPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterLocalizersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterPresentersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterCategoryItemCounterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterComparatorsPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterFilterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterFlatTranslatorPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterRendererPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
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
            ->addCompilerPass(new RegisterProductQueryFilterPass('product'))
            ->addCompilerPass(new RegisterProductQueryFilterPass('product_model'))
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass())
            ->addCompilerPass(new RegisterFilterPass())
            ->addCompilerPass(new RegisterComparatorsPass())
            ->addCompilerPass(new RegisterLocalizersPass())
            ->addCompilerPass(new RegisterPresentersPass())
            ->addCompilerPass(new RegisterSerializerPass('pim_internal_api_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_external_api_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_standard_format_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_indexing_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_storage_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_datagrid_serializer'))
            ->addCompilerPass(new RegisterSerializerPass('pim_serializer'))
            ->addCompilerPass(new RegisterRendererPass())
            ->addCompilerPass(new RegisterCategoryItemCounterPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass('product_and_product_model'))
            ->addCompilerPass(new ConfigureAxisValueLabelsNormalizerPass())
            ->addCompilerPass(new RegisterFlatTranslatorPass())
        ;

        $mappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/Product') => 'Akeneo\Pim\Enrichment\Component\Product\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/Category') => 'Akeneo\Pim\Enrichment\Component\Category\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/Comment') => 'Akeneo\Pim\Enrichment\Component\Comment\Model',
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

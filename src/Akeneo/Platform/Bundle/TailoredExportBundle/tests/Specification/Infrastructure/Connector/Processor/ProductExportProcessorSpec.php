<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\LabelCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Application\FilePathGenerator;
use Akeneo\Platform\TailoredExport\Application\ProductMapper;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\StringValue;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\MappedProductsWithFiles;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ValueCollectionHydrator;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductExportProcessorSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        ProductMapper $productMapper,
        FilePathGenerator $filePathGenerator
    ) {
        $this->beConstructedWith(
            $getAttributes,
            $getAssociationTypes,
            $valueCollectionHydrator,
            $columnCollectionHydrator,
            $productMapper,
            $filePathGenerator
        );
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
    }

    public function it_processes_product(
        ProductInterface $product,
        JobParameters $jobParameters,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        ColumnCollection $columnCollection,
        ProductMapper $productMapper,
        FilePathGenerator $filePathGenerator
    ) {
        $columns = [
            [
                'target' => 'categories-export',
                'sources' => [
                    [
                        'type' => 'property',
                        'code' => 'categories',
                        'locale' => null,
                        'channel' => null,
                        'selection' => [
                            'type' => 'code'
                        ],
                    ],
                ],
            ],
            [
                'target' => 'name-export',
                'sources' => [
                    [
                        'type' => 'attribute',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'selection' => [
                            'type' => 'code',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'association-export',
                'sources' => [
                    [
                        'type' => 'association_type',
                        'code' => 'X_SELL',
                        'locale' => null,
                        'channel' => null,
                        'selection' => [
                            'type' => 'code',
                            'entity_type' => 'products',
                            'separator' => ',',
                        ],
                    ],
                ],
            ],
        ];

        $name = $this->createAttribute('name');
        $crossSellAssociation = $this->createAssociationType('X_SELL');
        $valueCollection = new ValueCollection();
        $valueCollection->add(new StringValue('some_data'), 'name', null, null);
        $mappedProducts = [
            'categories-export' => 'my category',
            'name-export' => 'name value',
        ];

        $jobParameters->get('columns')->willReturn($columns);
        $getAttributes->forCodes(['name'])->willReturn(['name' => $name]);
        $getAssociationTypes->forCodes(['X_SELL'])->willReturn(['X_SELL' => $crossSellAssociation]);
        $columnCollectionHydrator->hydrate($columns, ['name' => $name], ['X_SELL' => $crossSellAssociation])->willReturn($columnCollection);
        $valueCollectionHydrator->hydrate($product, $columnCollection)->willReturn($valueCollection);

        $productMapper->map($columnCollection, $valueCollection)->willReturn($mappedProducts);
        $filePathGenerator->extract($columnCollection, $valueCollection)->willReturn([]);

        $mappedProductsWithFiles = new MappedProductsWithFiles($mappedProducts, []);

        $this->process($product)->shouldBeLike($mappedProductsWithFiles);
    }

    private function createAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            null,
            null,
            'text',
            []
        );
    }

    private function createAssociationType(string $code): AssociationType
    {
        return new AssociationType(
            $code,
            LabelCollection::fromArray([]),
            false,
            false,
        );
    }
}

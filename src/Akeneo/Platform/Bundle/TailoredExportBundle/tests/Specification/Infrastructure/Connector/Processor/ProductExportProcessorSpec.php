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
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractMediaQuery;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractMediaQueryHandler;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\ProcessedTailoredExport;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ValueCollectionHydrator;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductExportProcessorSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        MapValuesQueryHandler $mapValuesQueryHandler,
        ExtractMediaQueryHandler $extractMediaQueryHandler
    ) {
        $this->beConstructedWith(
            $getAttributes,
            $getAssociationTypes,
            $valueCollectionHydrator,
            $columnCollectionHydrator,
            $mapValuesQueryHandler,
            $extractMediaQueryHandler
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
        MapValuesQueryHandler $mapValuesQueryHandler,
        ExtractMediaQueryHandler $extractMediaQueryHandler
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
        $columnCollection = ColumnCollection::create([]);
        $columnCollectionHydrator->hydrate($columns, ['name' => $name], ['X_SELL' => $crossSellAssociation])
            ->willReturn($columnCollection);
        $valueCollectionHydrator->hydrate($product, $columnCollection)->willReturn($valueCollection);

        $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection))->willReturn($mappedProducts);
        $extractMediaQueryHandler->handle(new ExtractMediaQuery($columnCollection, $valueCollection))->willReturn([]);

        $processedTailoredExport = new ProcessedTailoredExport($mappedProducts, []);

        $this->process($product)->shouldBeLike($processedTailoredExport);
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

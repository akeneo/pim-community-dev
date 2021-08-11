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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractMediaQueryHandler;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ValueCollectionHydrator;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private GetAttributes $getAttributes;
    private GetAssociationTypesInterface $getAssociationTypes;
    private ValueCollectionHydrator $valueCollectionHydrator;
    private ColumnCollectionHydrator $columnCollectionHydrator;
    private MapValuesQueryHandler $mapValuesQueryHandler;
    private ExtractMediaQueryHandler $extractMediaQueryHandler;
    private ?StepExecution $stepExecution = null;
    private ?ColumnCollection $columnCollection = null;

    public function __construct(
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        MapValuesQueryHandler $mapValuesQueryHandler,
        ExtractMediaQueryHandler $extractMediaQueryHandler
    ) {
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->valueCollectionHydrator = $valueCollectionHydrator;
        $this->columnCollectionHydrator = $columnCollectionHydrator;
        $this->mapValuesQueryHandler = $mapValuesQueryHandler;
        $this->extractMediaQueryHandler = $extractMediaQueryHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException();
        }

        if (!$this->stepExecution instanceof StepExecution) {
            throw new \LogicException('Processor has not been properly initialized');
        }

        $columns = $this->stepExecution->getJobParameters()->get('columns');
        $columnCollection = $this->getColumnCollection($columns);
        $valueCollection = $this->valueCollectionHydrator->hydrate($product, $columnCollection);

        $mappedProducts = $this->mapValuesQueryHandler->handle($columnCollection, $valueCollection);
        $filesToExport = $this->extractMediaQueryHandler->handle($columnCollection, $valueCollection);

        return new ProcessedTailoredExport($mappedProducts, $filesToExport);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function getColumnCollection(array $columns): ColumnCollection
    {
        if (null === $this->columnCollection) {
            $indexedAttributes = $this->getIndexedAttributes($columns);
            $indexedAssociationTypes = $this->getIndexedAssociationTypes($columns);
            $this->columnCollection = $this->columnCollectionHydrator->hydrate($columns, $indexedAttributes, $indexedAssociationTypes);
        }

        return $this->columnCollection;
    }

    private function getIndexedAttributes(array $columns): array
    {
        $attributeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AttributeSource::TYPE === $source['type']) {
                    $attributeCodes[] = $source['code'];
                }
            }
        }

        return array_filter($this->getAttributes->forCodes(array_unique($attributeCodes)));
    }

    private function getIndexedAssociationTypes(array $columns): array
    {
        $associationTypeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AssociationTypeSource::TYPE === $source['type']) {
                    $associationTypeCodes[] = $source['code'];
                }
            }
        }

        $indexedAssociationTypes = $this->getAssociationTypes->forCodes(array_unique($associationTypeCodes));

        return array_filter($indexedAssociationTypes);
    }
}

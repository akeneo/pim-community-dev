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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Application\ProductMapper;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ValueCollectionHydrator;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private GetAttributes $getAttributes;
    private ValueCollectionHydrator $valueCollectionHydrator;
    private ColumnCollectionHydrator $columnCollectionHydrator;
    private ProductMapper $productMapper;
    private ?StepExecution $stepExecution = null;
    private ?ColumnCollection $columnCollection = null;

    public function __construct(
        GetAttributes $getAttributes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        ProductMapper $productMapper
    ) {
        $this->getAttributes = $getAttributes;
        $this->valueCollectionHydrator = $valueCollectionHydrator;
        $this->columnCollectionHydrator = $columnCollectionHydrator;
        $this->productMapper = $productMapper;
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

        return $this->productMapper->map($columnCollection, $valueCollection);
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
            $this->columnCollection = $this->columnCollectionHydrator->hydrate($columns, $indexedAttributes);
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
}

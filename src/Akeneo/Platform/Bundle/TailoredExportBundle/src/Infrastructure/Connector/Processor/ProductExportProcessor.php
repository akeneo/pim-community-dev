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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\SelectionHydrator;
use Akeneo\Platform\TailoredExport\Application\OperationApplier;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandler;
use Akeneo\Platform\TailoredExport\Domain\OperationCollection;
use Akeneo\Platform\TailoredExport\Domain\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Domain\SourceTypes;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ValueHydrator;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private SelectionHydrator $selectionHydrator;
    private GetAttributes $getAttributes;
    private ValueHydrator $valueHydrator;
    private OperationApplier $operationApplier;
    private SelectionHandler $selectionHandler;

    public function __construct(
        GetAttributes $getAttributes,
        SelectionHydrator $selectionHydrator,
        ValueHydrator $valueHydrator,
        OperationApplier $operationApplier,
        SelectionHandler $selectionHandler
    ) {
        $this->selectionHydrator = $selectionHydrator;
        $this->getAttributes = $getAttributes;
        $this->valueHydrator = $valueHydrator;
        $this->operationApplier = $operationApplier;
        $this->selectionHandler = $selectionHandler;
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
        $productStandard = [];

        foreach ($columns as $column) {
            $operationSourceValues = [];
            foreach ($column['sources'] as $source) {
                $operations = $this->createOperations($source['operations']);

                if (SourceTypes::ATTRIBUTE === $source['type']) {
                    $attribute = $this->getAttributes->forCode($source['code']);
                    $value = $this->valueHydrator->hydrateFromAttribute(
                        $product->getValue($source['code'], $source['locale'], $source['channel']),
                        $attribute,
                        $product
                    );
                    $selection = $this->selectionHydrator->createAttributeSelection($source['selection'], $attribute);
                } elseif (SourceTypes::PROPERTY === $source['type']) {
                    $propertyName = $source['code'];
                    $value = $this->valueHydrator->hydrateFromProperty($propertyName, $product);
                    $selection = $this->selectionHydrator->createPropertySelection(
                        $source['selection'],
                        $propertyName
                    );
                } else {
                    throw new \LogicException(sprintf('Source type "%s" is unsupported', $source['type']));
                }

                $value = $this->operationApplier->applyOperations($operations, $value);
                $operationSourceValues[] = $this->selectionHandler->applySelection(
                    $selection,
                    $value
                );
            }

            $productStandard[$column['target']] = implode(' ', $operationSourceValues);
        }

        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /** Use registry or move this to OperationCollection */
    private function createOperations($normalizedOperations): OperationCollection
    {
        $operations = [];
        foreach ($normalizedOperations as $normalizedOperation) {
            $operation = null;
            if ($normalizedOperation['type'] === 'replacement') {
                $operation = ReplacementOperation::createFromNormalized($normalizedOperation);
            }

            if ($operation) {
                $operations[] = $operation;
            }
        }

        return OperationCollection::create($operations);
    }
}

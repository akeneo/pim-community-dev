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
use Akeneo\Platform\TailoredExport\Application\OperationApplier;
use Akeneo\Platform\TailoredExport\Application\PropertyValueGetter;
use Akeneo\Platform\TailoredExport\Domain\OperationCollection;
use Akeneo\Platform\TailoredExport\Domain\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Domain\SourceTypes;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector\AttributeSelectorRegistry;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector\PropertySelectorRegistry;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private AttributeSelectorRegistry $attributeSelectorRegistry;
    private PropertySelectorRegistry $propertySelectorRegistry;
    private GetAttributes $getAttributes;
    private PropertyValueGetter $propertyValueGetter;
    private OperationApplier $operationApplier;

    public function __construct(
        AttributeSelectorRegistry $attributeSelectorRegistry,
        PropertySelectorRegistry $propertySelectorRegistry,
        GetAttributes $getAttributes,
        PropertyValueGetter $propertyValueGetter,
        OperationApplier $operationApplier
    ) {
        $this->attributeSelectorRegistry = $attributeSelectorRegistry;
        $this->propertySelectorRegistry = $propertySelectorRegistry;
        $this->getAttributes = $getAttributes;
        $this->propertyValueGetter = $propertyValueGetter;
        $this->operationApplier = $operationApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \LogicException('Invalid argument');
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
                    $value = $product->getValue($source['code'], $source['locale'], $source['channel']);
                    $attribute = $this->getAttributes->forCode($source['code']);

                    $operationSourceValues[] = $this->attributeSelectorRegistry->applyAttributeSelection(
                        $source['selection'],
                        $product,
                        $attribute,
                        $value
                    );
                } elseif (SourceTypes::PROPERTY === $source['type']) {
                    $sourceValue = $this->propertyValueGetter->get($source['code'], $product);
                    $sourceValue = $this->operationApplier->applyOperations($operations, $sourceValue);

                    $operationSourceValues[] = $this->propertySelectorRegistry->applyPropertySelection(
                        $source['selection'],
                        $sourceValue
                    );
                } else {
                    throw new \LogicException(sprintf('Source type "%s" is unsupported', $source['type']));
                }
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

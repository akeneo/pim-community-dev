<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TailoredExport\Connector\Processor\Operation\OperationHandler;
use Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector\ValueSelectorRegistry;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private OperationHandler $operationHandler;
    private GetAttributes $getAttributes;
    private ValueSelectorRegistry $valueSelectorRegistry;

    public function __construct(
        OperationHandler $operationHandler,
        GetAttributes $getAttributes,
        ValueSelectorRegistry $valueSelectorRegistry
    ) {
        $this->operationHandler = $operationHandler;
        $this->getAttributes = $getAttributes;
        $this->valueSelectorRegistry = $valueSelectorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \Exception('Invalid argument');
        }

        $productStandard = [];

        $columns = $this->stepExecution->getJobParameters()->get('columns') ?? [];
        foreach ($columns as $column) {
            $operationSourceValues = [];

            foreach ($column['sources'] as $source) {
                $value = $product->getValue($source['code'], $source['locale'], $source['channel']);

                $attribute = $this->getAttributes->forCode($source['code']);

                //TODO $attribute can be null
                $operationSourceValues[$source['uuid']] = $this->applyOperations($source['operations'], $attribute, $value);
                if (isset($source['selection'])) {
                    $operationSourceValues[$source['uuid']] = $this->applySelection($source['selection'], $attribute, $operationSourceValues[$source['uuid']]);
                }
            }

            $value = $this->applyFormat($column['format'], $operationSourceValues);

            $productStandard[$column['target']] = (string) $value;
        }

        return $productStandard;
    }

    private function applyOperations(array $operations, Attribute $attribute, ?ValueInterface $value)
    {
        return $this->operationHandler->handleOperations($operations, $attribute, $value);
    }

    private function applySelection(array $selection, Attribute $attribute, $value): string
    {
        return $this->valueSelectorRegistry->applySelection($selection, $attribute, $value);
    }

    private function applyFormat($format, $operationSourceValues)
    {
        $value = '';
        if ('concat' === $format['type']) {
            foreach ($format['elements'] as $element) {
                if ('source' === $element['type']) {
                    $value .= $operationSourceValues[$element['value']];
                } else {
                    $value .= $element['value'];
                }
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}

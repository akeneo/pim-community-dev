<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    protected StepExecution $stepExecution;

    public function __construct()
    {
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

                $operationSourceValues[$source['uuid']] = $this->applyOperation($source['operations'], $value);
                $operationSourceValues[$source['uuid']] = $this->applySelection($source['selection'], $operationSourceValues[$source['uuid']]);
            }

            $value = $this->applyFormat($column['format'], $operationSourceValues);

            $productStandard[$column['target']] = (string) $value;
        }

        return $productStandard;
    }

    private function applyOperation($operations, $value)
    {
        foreach ($operations as $operation) {
            if ('default_value' === $operation['type']
                && null === $value
            ) {
                $value = $operation['value'];
            }

//            if ('replace' === $operation['type']
//                && isset($operation['mapping'][$value])
//            ) {
//                $value = $operation['mapping'][$value];
//            }

            if ('convert' === $operation['type']) {
                $value = $operation['mapping'][$value];
            }
        }

        return $value;
    }

    private function applySelection($selection, $value)
    {
        $noramlizedValue = '';

        switch ($selection['type']) {
            case 'amount':
                $noramlizedValue = $value->getData();
//            default:
//                throw new \RuntimeException();
        }

        return $noramlizedValue;
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

<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TailoredExport\Connector\Processor\Operation\OperationHandler;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private OperationHandler $operationHandler;
    private GetAttributes $getAttributes;

    public function __construct(
        OperationHandler $operationHandler,
        GetAttributes $getAttributes
    ) {
        $this->operationHandler = $operationHandler;
        $this->getAttributes = $getAttributes;
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
                $operationSourceValues[$source['uuid']] = $this->applySelection($source['selection'], $operationSourceValues[$source['uuid']]);
            }

            $value = $this->applyFormat($column['format'], $operationSourceValues);

            $productStandard[$column['target']] = (string) $value;
        }

        return $productStandard;
    }

    private function applyOperations(array $operations, Attribute $attribute, ?ValueInterface $value)
    {
        $value = $this->operationHandler->handleOperations($operations, $attribute, $value);

        return $value;
    }

    private function applySelection($selection, $value)
    {
        // TODO $noramlizedValue = '';

        // switch ($selection['type']) {
        //     case 'amount':
        //         $noramlizedValue = $value->getData();
        //         //            default:
        //         //                throw new \RuntimeException();
        // }

        return $value;
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

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
        $productStandard['code'] = $product->getIdentifier();

        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}

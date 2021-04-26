<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \Exception('Invalid argument');
        }

        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Processor have not been properly initialized');
        }

        $this->stepExecution->incrementSummaryInfo('process');

        return ['id' => $product->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}

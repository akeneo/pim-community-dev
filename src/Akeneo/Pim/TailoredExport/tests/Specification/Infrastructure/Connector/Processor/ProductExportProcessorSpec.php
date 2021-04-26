<?php

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductExportProcessorSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    public function it_process_export(ProductInterface $product)
    {
        $product->getId()->willReturn(12);

        $this->process($product)->shouldReturn(['id' => 12]);
    }
}

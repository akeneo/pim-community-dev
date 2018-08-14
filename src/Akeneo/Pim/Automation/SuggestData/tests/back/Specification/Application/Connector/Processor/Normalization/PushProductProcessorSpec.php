<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\SuggestData\Application\Connector\Processor\Normalization\PushProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

class PushProductProcessorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PushProductProcessor::class);
    }

    public function it_is_an_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_processes_a_product_to_pimai_format(ProductInterface $product)
    {
        $this->process($product)->shouldReturn($product);
    }
}

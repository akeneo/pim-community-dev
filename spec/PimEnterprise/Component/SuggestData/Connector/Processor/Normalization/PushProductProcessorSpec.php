<?php

namespace spec\PimEnterprise\Component\SuggestData\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\SuggestData\Connector\Processor\Normalization\PushProductProcessor;

class PushProductProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PushProductProcessor::class);
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_a_product_to_pimai_format(ProductInterface $product)
    {
        $product->getIdentifier()->willReturn('product_blue');
        $this->process($product)->shouldReturn(['identifier' => 'product_blue']);
    }
}

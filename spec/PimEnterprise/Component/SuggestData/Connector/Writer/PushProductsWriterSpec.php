<?php

namespace spec\PimEnterprise\Component\SuggestData\Connector\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Connector\Writer\PushProductsWriter;

class PushProductsWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PushProductsWriter::class);
    }

    function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_writes_a_product()
    {
        $this->write(['identifier' => 'product_blue'])->shouldReturn(null);
    }
}

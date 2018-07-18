<?php

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Component\Connector\Writer\PushProductsWriter;

class PushProductsWriterSpec extends ObjectBehavior
{
    public function let(DataProviderFactory $dataProviderFactory, DataProviderAdapterInterface $dataProvider)
    {
        $dataProviderFactory->create()->willReturn($dataProvider);
        $this->beConstructedWith($dataProviderFactory, 100);
    }

    function it_is_initializable($dataProviderFactory, DataProviderAdapterInterface $dataProvider)
    {
        $this->shouldHaveType(PushProductsWriter::class);
    }

    function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_writes_a_product($dataProviderFactory, DataProviderAdapterInterface $dataProvider)
    {
        $this->write(['identifier' => 'product_blue'])->shouldReturn(null);
    }
}

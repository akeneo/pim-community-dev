<?php

namespace spec\Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

class ArrayReaderSpec extends ObjectBehavior
{
    function let(
        ItemReaderInterface $reader,
        ArrayConverterInterface $converter
    ) {
        $this->beConstructedWith($reader, $converter);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\ArrayReader');
    }

    function it_is_an_item_reader()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\ItemReaderInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_flushable()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\FlushableInterface');
    }

    function it_returns_null_with_no_elements(
        $reader
    ) {
        $reader->read()->willReturn(null);
        $this->read()->shouldBeNull();
    }

    function it_returns_element_one_by_one(
        $reader,
        $converter
    ) {
        $reader->read()->willReturn(['sku' => 'foo', 'attr' => 'baz,bar']);
        $converter->convert(['sku' => 'foo', 'attr' => 'baz,bar'])->willReturn([['code' => 'baz'], ['code' => 'bar']]);
        $this->read()->shouldEqual(['code' => 'baz']);
        $this->read()->shouldEqual(['code' => 'bar']);
    }
}

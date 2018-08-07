<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\ArrayReader;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;

class ArrayReaderSpec extends ObjectBehavior
{
    function let(
        FileReaderInterface $reader,
        ArrayConverterInterface $converter
    ) {
        $this->beConstructedWith($reader, $converter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ArrayReader::class);
    }

    function it_is_a_file_reader()
    {
        $this->shouldHaveType(FileReaderInterface::class);
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

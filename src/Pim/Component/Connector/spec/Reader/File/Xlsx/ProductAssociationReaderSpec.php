<?php

namespace spec\Pim\Component\Connector\Reader\File\Xlsx;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\Xlsx\ProductAssociationReader;
use Pim\Component\Connector\Reader\File\Xlsx\Reader;

class ProductAssociationReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAssociationReader::class);
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType(Reader::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldImplement(FlushableInterface::class);
    }
}

<?php

namespace spec\Pim\Component\Connector\Reader\File\Csv;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\Csv\ProductAssociationReader;
use Pim\Component\Connector\Reader\File\Csv\Reader;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileReaderInterface;

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

    function it_is_a_file_reader()
    {
        $this->shouldImplement(FileReaderInterface::class);
    }
}

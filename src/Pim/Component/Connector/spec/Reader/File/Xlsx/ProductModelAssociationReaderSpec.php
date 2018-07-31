<?php

declare(strict_types=1);

namespace spec\Pim\Component\Connector\Reader\File\Xlsx;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\Xlsx\ProductModelAssociationReader;
use Pim\Component\Connector\Reader\File\Xlsx\Reader;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileReaderInterface;

class ProductModelAssociationReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelAssociationReader::class);
    }

    function it_is_a_xlsx_reader()
    {
        $this->shouldHaveType(Reader::class);
    }

    function it_is_a_file_reader()
    {
        $this->shouldImplement(FileReaderInterface::class);
    }
}

<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv;

use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductModelAssociationReader;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

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

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType(Reader::class);
    }

    function it_is_a_file_reader()
    {
        $this->shouldImplement(FileReaderInterface::class);
    }
}

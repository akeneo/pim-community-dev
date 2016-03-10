<?php

namespace spec\Pim\Component\Connector\Reader\File;

use Box\Spout\Reader\ReaderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIterator;
use Pim\Component\Connector\Reader\File\MediaHelper;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(FileIterator $fileIterator, MediaHelper $helper)
    {
        $this->beConstructedWith($fileIterator, $helper, ['.', ','], ['Y-m-d', 'd-m-Y']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\XlsxProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\XlsxReader');
    }

    function it_transforms_media_paths_to_absolute_paths($fileIterator, $helper, ReaderInterface $reader)
    {
        $data = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

        $fileIterator->getReader()->willReturn($reader);
        $fileIterator->reset()->shouldBeCalled();
        $fileIterator->isInitialized()->willReturn(false);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->willReturn($data);

        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);
        $fileIterator->setFilePath($filePath)->willReturn($fileIterator);

        $absolutePath = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.jpg',
            'manual-fr_FR' => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.txt',
        ];

        $helper->transformMediaPathToAbsolute($data, $filePath)->willReturn($absolutePath);

        $this->read()->shouldReturn($absolutePath);
    }
}

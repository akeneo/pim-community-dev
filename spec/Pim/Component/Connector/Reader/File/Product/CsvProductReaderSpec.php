<?php

namespace spec\Pim\Component\Connector\Reader\File\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIterator;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Pim\Component\Connector\Reader\File\Product\MediaPathTransformer;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(FileIteratorFactory $fileIteratorFactory, MediaPathTransformer $mediaPath)
    {
        $this->beConstructedWith($fileIteratorFactory, $mediaPath, ['.', ','], ['Y-m-d', 'd-m-Y']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\Product\CsvProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\CsvReader');
    }

    function it_transforms_media_paths_to_absolute_paths(
        $fileIteratorFactory,
        FileIteratorInterface $fileIterator,
        $mediaPath
    ) {
        $data = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);
        $fileIteratorFactory->create($filePath, [
            'fieldDelimiter' => ';',
            'fieldEnclosure' => '"',
        ])->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->current()->willReturn($data);
        $fileIterator->valid()->willReturn(true);

        $absolutePath = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.jpg',
            'manual-fr_FR' => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.txt',
        ];

        $directoryPath = __DIR__ . '/../../../../../../features/Context/fixtures';
        $fileIterator->getDirectoryPath()->willReturn($directoryPath);
        $mediaPath->transform($data, $directoryPath)->willReturn($absolutePath);

        $this->read()->shouldReturn($absolutePath);
    }
}

<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['view', 'manual']);
        $this->beConstructedWith($attributeRepository, ['.', ','], ['Y-m-d', 'd-m-Y']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\CsvProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\CsvReader');
    }

    function it_transforms_media_paths_to_absolute_paths($fileIterator)
    {
        $data = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

        $fileIterator->setReaderOptions(
            [
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
            ]
        )->willReturn($fileIterator);
        $fileIterator->reset()->shouldBeCalled();
        $fileIterator->isInitialized()->willReturn(false);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->current()->willReturn($data);

        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);
        $fileIterator->setFilePath($filePath)->willReturn($fileIterator);

        $absolutePath = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.jpg',
            'manual-fr_FR' => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.txt',
        ];

        $this->read()->shouldReturn($absolutePath);
    }
}

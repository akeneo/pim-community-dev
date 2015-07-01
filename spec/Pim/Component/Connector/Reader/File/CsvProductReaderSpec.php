<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['view', 'manual']);
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\CsvProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\CsvReader');
    }

    function it_transforms_media_paths_to_absolute_paths()
    {
        $this->setFilePath(
            __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv'
        );

        $this->read()
            ->shouldReturn([
                'sku'          => 'SKU-001',
                'name'         => 'door',
                'view'         => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.jpg',
                'manual-fr_FR' => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.txt',
            ])
        ;
    }
}

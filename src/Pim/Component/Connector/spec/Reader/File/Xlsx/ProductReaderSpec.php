<?php

namespace spec\Pim\Component\Connector\Reader\File\Xlsx;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Pim\Component\Connector\Reader\File\MediaPathTransformer;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $arrayConverter,
        MediaPathTransformer $mediaPathTransformer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($fileIteratorFactory, $arrayConverter, $mediaPathTransformer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\Xlsx\ProductReader');
    }

    function it_is_a_xlsx_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\Xlsx\Reader');
    }

    function it_transforms_media_paths_to_absolute_paths(
        $fileIteratorFactory,
        $arrayConverter,
        $mediaPathTransformer,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    ) {
        $filePath = __DIR__ . '/../../../../../../../../features/Context/fixtures/with_media.csv';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('YYYY-mm-dd');

        $fileIteratorFactory->create($filePath, [])->willReturn($fileIterator);

        $item = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];
        $convertedItem = [
            'identifier' => 'SKU-001',
            'values' => [
                'sku' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'SKU-001',
                ],
                'name' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'door',
                ],
                'view' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'fixtures/sku-001.jpg',
                ],
                'manual' => [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => 'fixtures/sku-001.txt',
                ],
            ]
        ];
        $converterOptions = [
            'mapping' => [
                'family'     => 'family',
                'categories' => 'categories',
                'groups'     => 'groups',
            ],
            'with_associations' => false,
            'decimal_separator' => '.',
            'date_format'       => 'YYYY-mm-dd',
        ];

        $fileIterator->getHeaders()->willReturn(['sku', 'name', 'view', 'manual-fr_FR']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->current()->willReturn($item);
        $fileIterator->valid()->willReturn(true);
        $fileIterator->getDirectoryPath()->willReturn($filePath);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $arrayConverter->convert($item, $converterOptions)->willReturn($convertedItem);
        $mediaPathTransformer->transform($convertedItem['values'], $filePath)->shouldBeCalled();

        $this->read();
    }
}

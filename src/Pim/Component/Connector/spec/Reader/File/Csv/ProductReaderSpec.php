<?php

namespace spec\Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Pim\Component\Connector\Reader\File\MediaPathTransformer;

class  ProductReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        MediaPathTransformer $mediaPath,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter, $mediaPath);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\Csv\ProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Component\Connector\Reader\File\Csv\Reader');
    }

    function it_transforms_media_paths_to_absolute_paths(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters,
        $mediaPath
    ) {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('category');
        $jobParameters->get('groupsColumn')->willReturn('group');
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('YYYY-mm-dd');

        $data = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

        $fileIteratorFactory->create($filePath, [
            'reader_options' => [
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
            ]
        ])->willReturn($fileIterator);

        $fileIterator->getHeaders()->willReturn(['sku', 'name', 'view', 'manual-fr_FR']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->current()->willReturn($data);
        $fileIterator->valid()->willReturn(true);

        $absolutePath = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

        $directoryPath = __DIR__ . '/../../../../../../features/Context/fixtures';
        $fileIterator->getDirectoryPath()->willReturn($directoryPath);
        $mediaPath->transform($data, $directoryPath)->willReturn($absolutePath);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $converter->convert($absolutePath, [
            'mapping' => [
                'family' => 'family',
                'category' => 'categories',
                'group' => 'groups'
            ],
            'with_associations' => false,
            'decimal_separator' => '.',
            'date_format'       => 'YYYY-mm-dd',
        ])->willReturn($absolutePath);

        $this->read()->shouldReturn($absolutePath);
    }
}

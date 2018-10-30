<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductReader;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer;

class ProductReaderSpec extends ObjectBehavior
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
        $this->shouldHaveType(ProductReader::class);
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType(Reader::class);
    }

    function it_transforms_media_paths_to_absolute_paths(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters,
        $mediaPath
    ) {
        $filePath = __DIR__ . '/../../../../../../tests/legacy/features/Context/fixtures/with_media.csv';
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

        $directoryPath = __DIR__ . '/../../../../../../tests/legacy/features/Context/fixtures';
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

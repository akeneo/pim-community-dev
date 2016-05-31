<?php

namespace spec\Pim\Component\Connector\Reader\File\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArchiveStorage;
use Pim\Component\Connector\Reader\File\FileIterator;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Pim\Component\Connector\Reader\File\Product\MediaPathTransformer;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        MediaPathTransformer $mediaPath,
        StepExecution $stepExecution,
        ArchiveStorage $archiveStorage
    ) {
        $this->beConstructedWith($fileIteratorFactory, $archiveStorage, $mediaPath, ['.', ','], ['Y-m-d', 'd-m-Y']);
        $this->setStepExecution($stepExecution);
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
        $archiveStorage,
        $fileIteratorFactory,
        $stepExecution,
        $mediaPath,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters,
        JobExecution $jobExecution
    ) {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $archiveStorage->getPathname($jobExecution)->willReturn($filePath);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('delimiter')->willReturn(';');

        $data = [
            'sku'          => 'SKU-001',
            'name'         => 'door',
            'view'         => 'fixtures/sku-001.jpg',
            'manual-fr_FR' => 'fixtures/sku-001.txt',
        ];

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

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->read()->shouldReturn($absolutePath);
    }
}

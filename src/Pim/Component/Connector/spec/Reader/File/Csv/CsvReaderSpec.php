<?php

namespace spec\Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Prophecy\Argument;

class CsvReaderSpec extends ObjectBehavior
{
    function let(FileIteratorFactory $fileIteratorFactory, StepExecution $stepExecution)
    {
        $this->beConstructedWith($fileIteratorFactory);
        $this->setStepExecution($stepExecution);
    }

    function it_reads_csv_file(
        $fileIteratorFactory,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    ) {
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR  . 'with_media.csv';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('filePath')->willReturn($filePath);

        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $fileIteratorFactory->create($filePath, [
            'fieldDelimiter' => ';',
            'fieldEnclosure' => '"',
        ])->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->read()->shouldReturn($data);
    }

    private function getPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR .
               DIRECTORY_SEPARATOR  . 'features' .
               DIRECTORY_SEPARATOR  . 'Context' .
               DIRECTORY_SEPARATOR  . 'fixtures';
    }
}

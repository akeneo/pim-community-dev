<?php

namespace spec\Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArchiveStorage;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

class XlsxReaderSpec extends ObjectBehavior
{
    function let(FileIteratorFactory $fileIteratorFactory, StepExecution $stepExecution, ArchiveStorage $archiveStorage)
    {
        $this->beConstructedWith($fileIteratorFactory, $archiveStorage);
        $this->setStepExecution($stepExecution);
    }

    function it_read_csv_file(
        $archiveStorage,
        $fileIteratorFactory,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters,
        JobExecution $jobExecution
    ) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'features' .
            DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR . 'fixtures' .
            DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';


        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $archiveStorage->getPathname($jobExecution)->willReturn($filePath);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);

        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $fileIteratorFactory->create($filePath)->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->read()->shouldReturn($data);
    }
}

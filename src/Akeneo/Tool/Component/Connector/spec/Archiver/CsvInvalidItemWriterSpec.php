<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Archiver\CsvInvalidItemWriter;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CsvInvalidItemWriterSpec extends ObjectBehavior
{
    public function let(
        InvalidItemsCollector $collector,
        Writer $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem,
        DefaultValuesProviderInterface $defaultValuesProvider
    )
    {
        $this->beConstructedWith(
            $collector,
            $writer,
            $fileIteratorFactory,
            $filesystem,
            $defaultValuesProvider,
            'csv'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CsvInvalidItemWriter::class);
    }

    public function it_archives_job_execution_errors(
        JobExecution $jobExecution,
        FileIteratorFactory $fileIteratorFactory,
        FileIteratorInterface $csvIterator,
        JobInstance $jobInstance,
        AbstractAdapter $adapter,
        $collector,
        $filesystem,
        $writer
    )
    {
        $invalidItem1 = new FileInvalidItem([], 1);
        $invalidItem2 = new FileInvalidItem([], 2);
        $invalidItem3 = new FileInvalidItem([], 3);
        $collector->getInvalidItems()->willReturn([$invalidItem1, $invalidItem2, $invalidItem3]);

        $filePath = '/tmp/file.csv';
        $delimiter = ';';
        $enclosure = '"';
        $jobParameters = new JobParameters([
            'filePath' => $filePath,
            'delimiter' => $delimiter,
            'enclosure' => $enclosure,
        ]);

        $fileIteratorFactory->create(
            $filePath,
            ['reader_options' => [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure,
            ]]
        )->willReturn($csvIterator);

        $csvIterator->rewind()->shouldBeCalled();
        $csvIterator->valid()->willReturn(true, true, true, false);
        $csvIterator->next()->shouldBeCalledTimes(3);
        $csvIterator->current()->willReturn(
            ['line1-1', '', 'line1-2', ''],
            ['line2-1', '', 'line2-2', 'line2-3'],
            ['line3-1', 'line3-2', '', 'line2-3']
        );
        $csvIterator->getHeaders()->willReturn(['column1', '', 'column2', '']);

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(123456);

        $filesystem->put('//123456/invalid_csv/invalid_items.csv', '')->shouldBeCalled();
        $filesystem->getAdapter()->willReturn($adapter);

        $writer->setStepExecution(Argument::type(StepExecution::class))->shouldBeCalled();
        $writer->initialize()->shouldBeCalled();
        $writer->flush()->shouldBeCalled();
        $writer->write([
            [
                'column1' => 'line1-1',
                'column2' => 'line1-2',
            ],
            [
                'column1' => 'line2-1',
                'column2' => 'line2-2',
            ],
            [
                'column1' => 'line3-1',
                'column2' => '',
            ],
        ])->shouldBeCalled();

        $this->archive($jobExecution);
    }
}
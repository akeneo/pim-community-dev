<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File\Csv;

use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FileIteratorInterface $fileIterator,
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter);
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR  . 'with_media.csv';
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => $filePath]);
        $readerOptions = [
            'fieldDelimiter' => ';',
            'fieldEnclosure' => '"',
        ];
        $fileIteratorFactory->create($filePath, ['reader_options' => $readerOptions])->willReturn($fileIterator);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
        $this->initialize();
    }

    function it_returns_the_count_of_item_without_header(
        FileIteratorInterface $fileIterator,
    ) {
        $fileIterator->valid()->willReturn(true, true, true, false);
        $fileIterator->current()->willReturn(null);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();

        /** Expect 2 items, even there is 3 lines because the first one (the header) is ignored */
        $this->totalItems()->shouldReturn(2);
    }

    function it_reads_csv_file(
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
    ) {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $converter->convert($data, Argument::any())->willReturn($data);

        $this->read()->shouldReturn($data);
    }

    function it_resumes_csv_file_read_after_paused_job(
        FileIteratorInterface $fileIterator,
    ) {

        $this->setState(['position' => 4]);

        $fileIterator->next()->shouldBeCalledTimes(1);

        $this->initialize();
        $this->read();
    }

    function it_skips_an_item_in_case_of_conversion_error(
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
    ) {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $stepExecution->incrementSummaryInfo("skip")->shouldBeCalled();
        $converter->convert($data, Argument::any())->willThrow(
            new DataArrayConversionException('message', 0, null, new ConstraintViolationList())
        );

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }

    private function getPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR .
            DIRECTORY_SEPARATOR  . 'features' .
            DIRECTORY_SEPARATOR  . 'Context' .
            DIRECTORY_SEPARATOR  . 'fixtures';
    }
}

<?php

namespace spec\Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        StepExecution $stepExecution)
    {
        $this->beConstructedWith($fileIteratorFactory, $converter);
        $this->setStepExecution($stepExecution);
    }

    function it_reads_csv_file(
        $fileIteratorFactory,
        $converter,
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
            'reader_options' => [
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
            ]
        ])->willReturn($fileIterator);

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $converter->convert($data, Argument::any())->willReturn($data);

        $this->read()->shouldReturn($data);
    }

    function it_skips_an_item_in_case_of_conversion_error(
        $fileIteratorFactory,
        $converter,
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
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $fileIteratorFactory->create($filePath, [
            'reader_options' => [
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
            ]
        ])->willReturn($fileIterator);

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

        $this->shouldThrow('Pim\Component\Connector\Exception\InvalidItemFromViolationsException')->during('read');
    }

    private function getPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR .
               DIRECTORY_SEPARATOR  . 'features' .
               DIRECTORY_SEPARATOR  . 'Context' .
               DIRECTORY_SEPARATOR  . 'fixtures';
    }
}

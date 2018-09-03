<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File\Xlsx;

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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter);
        $this->setStepExecution($stepExecution);
    }

    function it_read_xlsx_file(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    ) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR .
            DIRECTORY_SEPARATOR . 'features' .
            DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR . 'fixtures' .
            DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);

        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $fileIteratorFactory->create($filePath, [])->willReturn($fileIterator);

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);
        $converter->convert($data, Argument::any())->willReturn($data);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $this->read()->shouldReturn($data);
    }

    function it_skips_an_item_in_case_of_conversion_error(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    ) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR .
            DIRECTORY_SEPARATOR . 'features' .
            DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR . 'fixtures' .
            DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);

        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $fileIteratorFactory->create($filePath, [])->willReturn($fileIterator);

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);
        $converter->convert($data, Argument::any())->willReturn($data);

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $stepExecution->incrementSummaryInfo("skip")->shouldBeCalled();
        $converter->convert($data, Argument::any())->willThrow(
            new DataArrayConversionException('message', 0, null, new ConstraintViolationList())
        );

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }


}

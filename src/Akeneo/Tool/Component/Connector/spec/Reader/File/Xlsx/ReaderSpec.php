<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File\Xlsx;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
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
    )
    {
        $this->beConstructedWith($fileIteratorFactory, $converter);
        $this->setStepExecution($stepExecution);
    }

    function it_returns_the_count_of_item_without_header(
        FileIteratorFactory $fileIteratorFactory,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters,
        StepExecution $stepExecution
    )
    {
        $filePath = __DIR__ . '/features/Context/fixtures/product_with_carriage_return.xlsx';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($filePath);
        $fileIterator->valid()->willReturn(true, true, true, false);
        $fileIterator->current()->willReturn(null);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIteratorFactory->create($filePath, [])->willReturn($fileIterator);

        /** Expect 2 items, even there is 3 lines because the first one (the header) is ignored */
        $this->totalItems()->shouldReturn(2);
    }

    function it_read_xlsx_file(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    )
    {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($this->initFilePath());

        $this->initFileIterator($fileIteratorFactory, $fileIterator);
        $converter->convert($this->initXlsData(), Argument::any())->willReturn($this->initXlsData());

        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $this->read()->shouldReturn($this->initXlsData());
    }

    function it_skips_an_item_in_case_of_conversion_error(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    )
    {
        $this->initStepExecution($stepExecution, $jobParameters);

        $this->initFileIterator($fileIteratorFactory, $fileIterator);

        $converter->convert($this->initXlsData(), Argument::any())->willThrow(
            new DataArrayConversionException('message', 0, null, new ConstraintViolationList())
        );
        $stepExecution->incrementSummaryInfo("skip")->shouldBeCalled();

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('read');

    }

    function it_skips_an_item_in_case_of_business_exception_error(
        $fileIteratorFactory,
        $converter,
        $stepExecution,
        FileIteratorInterface $fileIterator,
        JobParameters $jobParameters
    )    {
        $this->initStepExecution($stepExecution, $jobParameters);
        $this->initFileIterator($fileIteratorFactory, $fileIterator);

        $converter->convert($this->initXlsData(), Argument::any())->willThrow(
            new BusinessArrayConversionException('message','messageKey',[])
        );

        $this->shouldThrow(InvalidItemException::class)->during('read');
    }

    /**
     * @return string[]
     */
    private function initXlsData(): array
    {
        return [
            'sku' => 'SKU-001',
            'name' => 'door',
        ];
    }

    /**
     * @return string
     */
    private function initFilePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR .
            DIRECTORY_SEPARATOR . 'features' .
            DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR . 'fixtures' .
            DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';
    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator $stepExecution
     * @param $jobParameters
     */
    private function initStepExecution(\PhpSpec\Wrapper\Collaborator $stepExecution, $jobParameters): void
    {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($this->initFilePath());

        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();


    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator $fileIteratorFactory
     * @param $fileIterator
     */
    private function initFileIterator(\PhpSpec\Wrapper\Collaborator $fileIteratorFactory, $fileIterator): void
    {
        $fileIteratorFactory->create($this->initFilePath(), [])->willReturn($fileIterator);

        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);

        $fileIterator->current()->willReturn($this->initXlsData());
    }
}

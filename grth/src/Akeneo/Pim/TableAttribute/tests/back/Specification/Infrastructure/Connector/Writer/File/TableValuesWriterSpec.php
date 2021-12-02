<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File;

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValuesTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File\TableValuesWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TableValuesWriterSpec extends ObjectBehavior
{
    function let(
        ItemWriterInterface $decoratedWriter,
        TableValuesTranslator $tableValuesTranslator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($decoratedWriter, $tableValuesTranslator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValuesWriter::class);
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_writes_items_without_labels(
        ItemWriterInterface $decoratedWriter,
        TableValuesTranslator $tableValuesTranslator,
        StepExecution $stepExecution
    ) {
        $items = ['items'];
        $jobParameters = new JobParameters([
            'with_label' => false,
            'file_locale' => 'en_US',
            'header_with_label' => false,
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $tableValuesTranslator->translate(Argument::cetera())->shouldNotBeCalled();
        $decoratedWriter->write($items)->shouldBeCalledOnce();

        $this->write($items);
    }

    function it_writes_items_with_labels(
        ItemWriterInterface $decoratedWriter,
        TableValuesTranslator $tableValuesTranslator,
        StepExecution $stepExecution
    ) {
        $items = ['items'];
        $jobParameters = new JobParameters([
            'with_label' => true,
            'file_locale' => 'en_US',
            'header_with_label' => false,
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $tableValuesTranslator->translate($items, 'en_US', false)->shouldBeCalledOnce()->willReturn(['translated items']);
        $decoratedWriter->write(['translated items'])->shouldBeCalledOnce();

        $this->write($items);
    }
}

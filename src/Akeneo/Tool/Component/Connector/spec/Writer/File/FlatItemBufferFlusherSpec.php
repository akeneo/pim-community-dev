<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;
use Symfony\Component\Filesystem\Filesystem;

class FlatItemBufferFlusherSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $directory;

    function it_is_initializable()
    {
        $this->shouldHaveType(FlatItemBufferFlusher::class);
    }

    function let(ColumnSorterInterface $columnSorter, StepExecution $stepExecution)
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $this->beConstructedWith($columnSorter);

        $this->setStepExecution($stepExecution);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_flushes_a_buffer_without_a_max_number_of_lines($columnSorter, FlatItemBuffer $buffer, StepExecution $stepExecution, JobParameters $parameters)
    {
        $columnSorter->sort(Argument::any(), [])->willReturn(['colA', 'colB']);

        $stepExecution->getJobParameters()->willReturn($parameters);
        $stepExecution->incrementSummaryInfo('write')->shouldBeCalled();
        $parameters->all()->willReturn([]);

        $buffer->key()->willReturn(0);
        $buffer->rewind()->willReturn();
        $buffer->valid()->willReturn(true, false);
        $buffer->next()->willReturn();
        $buffer->current()->willReturn(['fooA', 'fooB']);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->flush($buffer, ['type' => 'csv'], $this->directory . 'output');

        if (!file_exists($this->directory . 'output')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output')
            );
        }
    }

    function it_flushes_a_buffer_into_multiple_files_without_extension($columnSorter, FlatItemBuffer $buffer, $filesystem, StepExecution $stepExecution, JobParameters $parameters)
    {
        $columnSorter->sort(Argument::any(), [])->willReturn(['colA', 'colB']);

        $stepExecution->getJobParameters()->willReturn($parameters);
        $stepExecution->incrementSummaryInfo('write')->shouldBeCalled();
        $parameters->all()->willReturn([]);

        $buffer->rewind()->willReturn();
        $buffer->count()->willReturn(3);
        $buffer->valid()->willReturn(true, true, true, false);
        $buffer->next()->willReturn();
        $buffer->current()->willReturn([
            'colA' => 'fooA',
            'colB' => 'fooB'
        ]);
        $buffer->key()->willReturn(0);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->flush($buffer, ['type' => 'csv'], $this->directory . 'output', 2);

        if (!file_exists($this->directory . 'output_1')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_1')
            );
        }

        if (!file_exists($this->directory . 'output_2')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_2')
            );
        }
    }

    function it_flushes_a_buffer_into_multiple_files_with_extension($columnSorter, FlatItemBuffer $buffer, StepExecution $stepExecution, JobParameters $parameters)
    {
        $columnSorter->sort(Argument::any(), [])->willReturn(['colA', 'colB']);

        $stepExecution->getJobParameters()->willReturn($parameters);
        $stepExecution->incrementSummaryInfo('write')->shouldBeCalled();
        $parameters->all()->willReturn([]);

        $buffer->rewind()->willReturn();
        $buffer->count()->willReturn(3);
        $buffer->valid()->willReturn(true, true, true, false);
        $buffer->next()->willReturn();
        $buffer->current()->willReturn([
            'colA' => 'fooA',
            'colB' => 'fooB'
        ]);
        $buffer->key()->willReturn(0);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->flush($buffer, ['type' => 'csv'], $this->directory . 'output.txt', 2);

        if (!file_exists($this->directory . 'output_1.txt')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_1.txt')
            );
        }

        if (!file_exists($this->directory . 'output_2.txt')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_2.txt')
            );
        }
    }

    function it_throws_an_exception_if_type_is_not_defined($columnSorter, FlatItemBuffer $buffer, StepExecution $stepExecution, JobParameters $parameters)
    {
        $columnSorter->sort(Argument::any(), [])->willReturn(['colA', 'colB']);

        $stepExecution->getJobParameters()->willReturn($parameters);
        $parameters->all()->willReturn([]);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->shouldThrow('InvalidArgumentException')
            ->during('flush', [$buffer, [], Argument::any()]);
    }

    function it_throws_an_exception_if_type_is_not_recognized($columnSorter, FlatItemBuffer $buffer, StepExecution $stepExecution, JobParameters $parameters)
    {
        $columnSorter->sort(Argument::any(), [])->willReturn(['colA', 'colB']);

        $stepExecution->getJobParameters()->willReturn($parameters);
        $parameters->all()->willReturn([]);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->shouldThrow('Box\Spout\Common\Exception\UnsupportedTypeException')
            ->during('flush', [$buffer, ['type' => 'undefined'], Argument::any()]);
    }
}

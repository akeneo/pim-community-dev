<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
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
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\FlatItemBufferFlusher');
    }

    function let(FilePathResolverInterface $filePathResolver, ColumnSorterInterface $columnSorter)
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $this->beConstructedWith($filePathResolver, $columnSorter);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_flushes_a_buffer_without_a_max_number_of_lines($columnSorter, FlatItemBuffer $buffer)
    {
        $columnSorter->sort(Argument::any())->willReturn(['colA', 'colB']);

        $buffer->getBuffer()->willReturn([['fooA', 'fooB'], ['barA', 'barB'], ['bazA', 'bazB']]);
        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->flush($buffer, ['type' => 'csv'], $this->directory . 'output');

        if (!file_exists($this->directory . 'output')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output')
            );
        }
    }

    function it_flushes_a_buffer_into_multiple_files_without_extension($columnSorter, $filePathResolver, FlatItemBuffer $buffer)
    {
        $columnSorter->sort(Argument::any())->willReturn(['colA', 'colB']);

        $buffer->getBuffer()->willReturn([['fooA', 'fooB'], ['barA', 'barB'], ['bazA', 'bazB']]);
        $buffer->getHeaders()->willReturn(['colA', 'colB']);
        $buffer->count()->willReturn(3);

        $filePathResolver->resolve('/tmp/spec/output%fileNb%', ['parameters' => ['%fileNb%' => '_1']])
            ->willReturn('/tmp/spec/output_1');
        $filePathResolver->resolve('/tmp/spec/output%fileNb%', ['parameters' => ['%fileNb%' => '_2']])
            ->willReturn('/tmp/spec/output_2');

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

    function it_flushes_a_buffer_into_multiple_files_with_extension($columnSorter, $filePathResolver, FlatItemBuffer $buffer)
    {
        $columnSorter->sort(Argument::any())->willReturn(['colA', 'colB']);

        $buffer->getBuffer()->willReturn([['fooA', 'fooB'], ['barA', 'barB'], ['bazA', 'bazB']]);
        $buffer->getHeaders()->willReturn(['colA', 'colB']);
        $buffer->count()->willReturn(3);

        $filePathResolver->resolve('/tmp/spec/output%fileNb%.txt', ['parameters' => ['%fileNb%' => '_1']])
            ->willReturn('/tmp/spec/output_1.txt');
        $filePathResolver->resolve('/tmp/spec/output%fileNb%.txt', ['parameters' => ['%fileNb%' => '_2']])
            ->willReturn('/tmp/spec/output_2.txt');

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

    function it_throws_an_exception_if_type_is_not_defined($columnSorter, FlatItemBuffer $buffer)
    {
        $columnSorter->sort(Argument::any())->willReturn(['colA', 'colB']);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->shouldThrow('InvalidArgumentException')
            ->during('flush', [$buffer, [], Argument::any()]);
    }

    function it_throws_an_exception_if_type_is_not_recognized($columnSorter, FlatItemBuffer $buffer)
    {
        $columnSorter->sort(Argument::any())->willReturn(['colA', 'colB']);

        $buffer->getHeaders()->willReturn(['colA', 'colB']);

        $this->shouldThrow('Box\Spout\Common\Exception\UnsupportedTypeException')
            ->during('flush', [$buffer, ['type' => 'undefined'], Argument::any()]);
    }
}

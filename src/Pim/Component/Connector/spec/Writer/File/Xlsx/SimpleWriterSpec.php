<?php

namespace spec\Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;

class SimpleWriterSpec extends ObjectBehavior
{
    function let(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        FlatItemBufferFlusher $flusher
    ) {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $flusher);

        $filePathResolver
            ->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.xlsx');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Xlsx\SimpleWriter');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_prepares_items_to_write(
        $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn(true);
        $jobParameters->has('mainContext')->willReturn(false);

        $groups = [
            [
                'code'        => 'promotion',
                'type'        => 'RELATED',
                'label-en_US' => 'Promotion',
                'label-de_DE' => 'Förderung'
            ],
            [
                'code'        => 'related',
                'type'        => 'RELATED',
                'label-en_US' => 'Related',
                'label-de_DE' => 'Verbunden'
            ]
        ];

        $flatRowBuffer->write(
            [
                [
                    'code'        => 'promotion',
                    'type'        => 'RELATED',
                    'label-en_US' => 'Promotion',
                    'label-de_DE' => 'Förderung'
                ],
                [
                    'code'        => 'related',
                    'type'        => 'RELATED',
                    'label-en_US' => 'Related',
                    'label-de_DE' => 'Verbunden'
                ]
            ],
            true
        )->shouldBeCalled();

        $this->write($groups);
    }

    function it_writes_the_xlsx_file(
        $flusher,
        $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('linesPerFile')->willReturn(2);
        $jobParameters->get('filePath')->willReturn('my/file/path/foo');
        $jobParameters->has('mainContext')->willReturn(false);

        $flusher->flush(
            $flatRowBuffer,
            Argument::type('string'),
            2,
            Argument::type('array')
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->flush();
    }
}

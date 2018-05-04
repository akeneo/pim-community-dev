<?php

namespace spec\Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Prophecy\Argument;

class WriterSpec extends ObjectBehavior
{
    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher
    ) {
        $this->beConstructedWith($arrayConverter, $bufferFactory, $flusher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Xlsx\Writer');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Item\ItemWriterInterface');
    }

    function it_prepares_items_to_write(
        $arrayConverter,
        $bufferFactory,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Group export');
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn('%job_label%_group.xlsx');
        $jobParameters->has('ui_locale')->willReturn(false);

        $groups = [
            [
                'code'   => 'promotion',
                'type'   => 'RELATED',
                'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung']
            ],
            [
                'code'   => 'related',
                'type'   => 'RELATED',
                'labels' => ['en_US' => 'Related', 'de_DE' => 'Verbunden']
            ]
        ];

        $arrayConverter->convert([
            'code'   => 'promotion',
            'type'   => 'RELATED',
            'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung']
        ])->willReturn([
            'code'        => 'promotion',
            'type'        => 'RELATED',
            'label-en_US' => 'Promotion',
            'label-de_DE' => 'Förderung'
        ]);

        $arrayConverter->convert([
            'code'   => 'related',
            'type'   => 'RELATED',
            'labels' => ['en_US' => 'Related', 'de_DE' => 'Verbunden']
        ])->willReturn([
            'code'        => 'related',
            'type'        => 'RELATED',
            'label-en_US' => 'Related',
            'label-de_DE' => 'Verbunden'
        ]);

        $bufferFactory->create()->willReturn($flatRowBuffer);
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
            ['withHeader' => true]
        )->shouldBeCalled();

        $this->initialize();
        $this->write($groups);
    }

    function it_writes_the_xlsx_file(
        $bufferFactory,
        $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Group export');
        $jobParameters->get('linesPerFile')->willReturn(2);
        $jobParameters->get('filePath')->willReturn('my/file/path/foo/%job_label%_group.xlsx');
        $jobParameters->has('ui_locale')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            2
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->initialize();
        $this->flush();
    }
}

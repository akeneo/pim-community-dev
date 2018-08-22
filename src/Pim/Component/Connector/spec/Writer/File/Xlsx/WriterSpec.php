<?php

namespace spec\Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;

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
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
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
        $stepExecution->getStartTime()->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Group export');
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn(sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.xlsx');
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
        $stepExecution->getStartTime()->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Group export');
        $jobParameters->get('linesPerFile')->willReturn(1);
        $jobParameters->get('filePath')->willReturn(sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.xlsx');
        $jobParameters->has('ui_locale')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $this->initialize();
        $flusher->flush(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00.xlsx',
            1
        )->willReturn(
            [
                sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_1.xlsx',
                sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_2.xlsx',
            ]
        );

        $this->flush();

        $this->getWrittenFiles()->shouldReturn(
            [
                sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_1.xlsx' => 'XLSX_Group_export_1967-08-05_15-15-00_1.xlsx',
                sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_2.xlsx' => 'XLSX_Group_export_1967-08-05_15-15-00_2.xlsx',
            ]
        );
    }
}

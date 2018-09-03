<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File\Csv;

use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

class WriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Writer::class);
    }

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher
    ) {
        $this->beConstructedWith($arrayConverter, $bufferFactory, $flusher);
    }

    function it_is_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_prepares_the_export(
        $arrayConverter,
        $bufferFactory,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('job_label');
        $jobParameters->get('filePath')->willReturn(sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->get('withHeader')->willReturn(true);

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

        $bufferFactory->create()->willReturn($flatRowBuffer);

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

    function it_writes_the_csv_file(
        $bufferFactory,
        $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn(sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('job_label');

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flatRowBuffer->rewind()->willReturn();
        $flatRowBuffer->valid()->willReturn(true, false);
        $flatRowBuffer->next()->willReturn();
        $flatRowBuffer->current()->willReturn([
            'id' => 0,
            'family' => 45
        ]);

        $this->initialize();
        $flusher->flush(
            $flatRowBuffer,
            [
                'type'           => 'csv',
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
                'shouldAddBOM'   => false,
            ],
            sys_get_temp_dir() . '/my/file/path/job_label_1967-08-05_15-15-00.csv',
            -1
        )->willReturn(
            [
                sys_get_temp_dir() . '/my/file/path/job_label_1967-08-05_15-15-00.csv'
            ]
        );

        $this->flush();

        $this->getWrittenFiles()->shouldReturn(
            [
                sys_get_temp_dir() . '/my/file/path/job_label_1967-08-05_15-15-00.csv' => 'job_label_1967-08-05_15-15-00.csv',
            ]
        );
    }
}

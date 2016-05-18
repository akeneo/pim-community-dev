<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class XlsxSimpleWriterSpec extends ObjectBehavior
{
    function let(FilePathResolverInterface $filePathResolver, FlatItemBuffer $flatRowBuffer, ColumnSorterInterface $columnSorter)
    {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $columnSorter, 10000);

        $filePathResolver
            ->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.xlsx');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\XlsxSimpleWriter');
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

        $flatRowBuffer->write([
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
        ], true)->shouldBeCalled();

        $this->write($groups);
    }

    function it_writes_the_xlsx_file(
        $flatRowBuffer,
        $columnSorter,
        BufferInterface $buffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn(true);
        $jobParameters->get('linesPerFile')->willReturn(10000);
        $jobParameters->has('mainContext')->willReturn(false);

        $flatRowBuffer->count()->willReturn(10);
        $flatRowBuffer->getHeaders()->willReturn(['code', 'type', 'label-en_US', 'label-de_DE']);
        $flatRowBuffer->getBuffer()->willReturn($buffer);

        $columnSorter->sort([
            'code',
            'type',
            'label-en_US',
            'label-de_DE'
        ])->willReturn([
            'code',
            'label-en_US',
            'label-de_DE',
            'type'
        ]);

        $this->flush();
    }
}

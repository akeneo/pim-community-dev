<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;

class XlsxSimpleWriterSpec extends ObjectBehavior
{
    function let(FilePathResolverInterface $filePathResolver, FlatItemBuffer $flatRowBuffer)
    {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer);

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

    function it_prepares_items_to_write($flatRowBuffer, StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);

        $this->setWithHeader(true);
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

    function it_writes_the_xlsx_file($flatRowBuffer, BufferInterface $buffer)
    {
        $flatRowBuffer->getHeaders()->willReturn(['code', 'type', 'label-en_US', 'label-de_DE']);
        $flatRowBuffer->getBuffer()->willReturn($buffer);

        $this->flush();
    }

    function it_has_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.export.filePath.label',
                    'help'  => 'pim_connector.export.filePath.help'
                ]
            ],
            'withHeader' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.export.withHeader.label',
                    'help'  => 'pim_connector.export.withHeader.help'
                ]
            ],
        ]);
    }
}

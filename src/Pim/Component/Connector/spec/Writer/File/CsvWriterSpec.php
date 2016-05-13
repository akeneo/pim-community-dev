<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;

class CsvWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\CsvWriter');
    }

    function let(FilePathResolverInterface $filePathResolver, FlatItemBuffer $flatRowBuffer, ColumnSorterInterface $columnSorter)
    {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $columnSorter);

        $filePathResolver->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.csv');
    }

    function it_is_a_configurable_step()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_prepares_the_export($flatRowBuffer)
    {
        $this->setWithHeader(true);

        $items = [
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ];

        $flatRowBuffer->write($items, true)->shouldBeCalled();

        $this->write($items);
    }

    function it_writes_the_csv_file($flatRowBuffer, BufferInterface $buffer, $columnSorter)
    {
        $flatRowBuffer->getHeaders()->willReturn(['id', 'family']);
        $flatRowBuffer->getBuffer()->willReturn($buffer);

        $columnSorter->sort(['id', 'family'])->willReturn(['id', 'family']);

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
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.export.delimiter.label',
                    'help'  => 'pim_connector.export.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.export.enclosure.label',
                    'help'  => 'pim_connector.export.enclosure.help'
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

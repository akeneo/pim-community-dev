<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Prophecy\Argument;

class XlsxVariantGroupWriterSpec extends ObjectBehavior
{
    function let(FilePathResolverInterface $filePathResolver, FlatItemBuffer $flatRowBuffer, BulkFileExporter $mediaCopier)
    {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $mediaCopier);

        $filePathResolver->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.xlsx');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\XlsxVariantGroupWriter');
    }

    function it_is_a_configurable_step()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
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

    function it_prepares_the_export($flatRowBuffer, $mediaCopier, StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);

        $this->setWithHeader(true);
        $items = [
            [
                'variant_group' => [
                    'code'        => 'jackets',
                    'axis'        => 'size,color',
                    'type'        => 'variant',
                    'label-en_US' => 'Jacket',
                    'label-en_GB' => 'Jacket'
                ],
                'media' => [
                    'filePath'     => 'wrong/path',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                'variant_group' => [
                    'code'        => 'sweaters',
                    'type'        => 'variant',
                    'label-en_US' => 'Sweaters',
                    'label-en_GB' => 'Chandails'
                ],
                'media' => [
                    'filePath'     => 'img/variant_group1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ]
        ];

        $flatRowBuffer->write([
            [
                'code'        => 'jackets',
                'axis'        => 'size,color',
                'type'        => 'variant',
                'label-en_US' => 'Jacket',
                'label-en_GB' => 'Jacket'
            ],
            [
                'code'        => 'sweaters',
                'type'        => 'variant',
                'label-en_US' => 'Sweaters',
                'label-en_GB' => 'Chandails'
            ],
        ], true)->shouldBeCalled();

        $mediaCopier->exportAll([
            [
                'filePath'     => 'wrong/path',
                'exportPath'   => 'export',
                'storageAlias' => 'storageAlias',
            ],
            [
                'filePath'     => 'img/variant_group1.jpg',
                'exportPath'   => 'export',
                'storageAlias' => 'storageAlias',
            ],
        ], '/tmp/export')->shouldBeCalled();

        $mediaCopier->getErrors()->willReturn([
            [
                'medium' => [
                    'filePath'     => 'wrong/path',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
                'message' => 'Error message',
            ]
        ]);
        $mediaCopier->getCopiedMedia()->willReturn([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => [
                    'filePath'     => 'img/variant_group1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ]
        ]);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }

    function it_writes_the_xlsx_file($flatRowBuffer, BufferInterface $buffer)
    {
        $flatRowBuffer->getHeaders()->willReturn(['id', 'family']);
        $flatRowBuffer->getBuffer()->willReturn($buffer);

        $this->flush();
    }
}

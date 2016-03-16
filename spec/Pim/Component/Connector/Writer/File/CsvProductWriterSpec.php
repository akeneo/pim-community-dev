<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferInterface;
use Pim\Component\Connector\Writer\File\BulkFileExporterInterface;
use Prophecy\Argument;

class CsvProductWriterSpec extends ObjectBehavior
{
    function let(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $mediaCopier
    ) {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $mediaCopier);

        $filePathResolver->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.csv');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\CsvProductWriter');
    }

    function it_prepares_the_export($flatRowBuffer, $mediaCopier)
    {
        $this->setWithHeader(true);

        $flatRowBuffer->write([
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ], true)->shouldBeCalled();

        $mediaCopier->exportAll([
            [
                'filePath' => null,
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
            [
                'filePath' => 'img/product1.jpg',
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
        ], '/tmp/export')->shouldBeCalled();

        $mediaCopier->getErrors()->willReturn([]);
        $mediaCopier->getCopiedMedia()->willReturn([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => [
                    'filePath'     => 'img/product1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ]
        ]);

        $this->write([
            [
                'product' => [
                    'id' => 123,
                    'family' => 12,
                ],
                'media' => [
                    'filePath' => null,
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                'product' => [
                    'id' => 165,
                    'family' => 45,
                ],
                'media' => [
                    'filePath' => 'img/product1.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ]
        ]);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }
}

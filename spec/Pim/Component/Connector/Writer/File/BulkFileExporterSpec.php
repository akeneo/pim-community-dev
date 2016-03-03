<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\FileExporterInterface;
use Prophecy\Argument;

class BulkFileExporterSpec extends ObjectBehavior
{
    function let(FileExporterInterface $fileExporter)
    {
        $this->beConstructedWith($fileExporter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\BulkFileExporter');
    }

    function it_copies_media_to_the_export_dir($fileExporter)
    {
        $fileExporter->export('img/product.jpg', '/tmp/export', 'storageAlias')->shouldBeCalled();
        $fileExporter->export(null, '/tmp/export', 'storageAlias')->shouldNotBeCalled();

        $this->exportAll([
            [
                [
                    'filePath' => 'img/product.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                [
                    'filePath' => null,
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
        ], '/tmp');

        $this->getErrors()->shouldHaveCount(0);
    }

    function it_copy_media_to_the_export_dir($fileExporter)
    {
        $fileExporter->export('img/product.jpg', '/tmp/export', 'storageAlias')->willThrow('Akeneo\Component\FileStorage\Exception\FileTransferException');
        $fileExporter->export(null, '/tmp/export', 'storageAlias')->willThrow('\LogicException');

        $this->exportAll([
            [
                [
                    'filePath' => 'img/product.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ],
            [
                [
                    'filePath' => 'img/product.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
        ], '/tmp');

        $this->getErrors()->shouldHaveCount(2);
    }
}

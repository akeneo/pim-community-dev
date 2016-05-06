<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
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
                    'filePath'     => 'img/product.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                [
                    'filePath'     => null,
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
        ], '/tmp');

        $this->getErrors()->shouldHaveCount(0);
        $this->getCopiedMedia()->shouldBeEqualTo([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => [
                    'filePath'     => 'img/product.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias'
                ]
            ]
        ]);
    }

    function it_allows_to_get_errors_if_the_copy_went_wrong($fileExporter)
    {
        $fileExporter
            ->export('img/product.jpg', '/tmp/export', 'storageAlias')
            ->willThrow(new FileTransferException());
        $fileExporter
            ->export('wrong/-path.foo', '/tmp/export', 'storageAlias')
            ->willThrow(new \LogicException('Something went wrong.'));

        $this->exportAll([
            [
                [
                    'filePath'     => 'img/product.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ],
            [
                [
                    'filePath'     => 'wrong/-path.foo',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
        ], '/tmp');

        $this->getErrors()->shouldBeEqualTo([
            [
                'message' => 'The media has not been found or is not currently available',
                'medium'  => [
                    'filePath'     => 'img/product.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ],
            [
                'message' => 'The media has not been copied. Something went wrong.',
                'medium'  => [
                    'filePath'     => 'wrong/-path.foo',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ]
        ]);
    }
}

<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FileStorage;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;

class FileExporterSpec extends ObjectBehavior
{
    function let(MountManager $mountManager, FileFetcherInterface $fileFetcher)
    {
        $this->beConstructedWith($mountManager, $fileFetcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\FileExporter');
    }

    function it_exports_a_file($mountManager, $fileFetcher, Filesystem $filesystem)
    {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $rawFile = new \SplFileInfo($pathname);

        if (!is_dir(sys_get_temp_dir() . '/spec/export')) {
            mkdir(sys_get_temp_dir() . '/spec/export', 0777, true);
        }

        $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($filesystem);
        $fileFetcher->fetch($filesystem, '1/2/3/123_file.txt')->willReturn($rawFile);

        $this->export('1/2/3/123_file.txt', sys_get_temp_dir() . '/spec/export/file.txt', FileStorage::CATALOG_STORAGE_ALIAS);

        if (!file_exists(sys_get_temp_dir() . '/spec/export/file.txt')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been exported', sys_get_temp_dir() . '/spec/export/file.txt')
            );
        }

        if (file_exists($pathname)) {
            throw new FailedPredictionException(sprintf('File "%s" should have been deleted', $pathname));
        }

        unlink(sys_get_temp_dir() . '/spec/export/file.txt');
    }
}

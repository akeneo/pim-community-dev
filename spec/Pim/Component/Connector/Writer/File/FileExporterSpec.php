<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;

class FileExporterSpec extends ObjectBehavior
{
    function let(MountManager $mountManager, RawFileFetcherInterface $fileFetcher)
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

        $mountManager->getFilesystem('storage')->willReturn($filesystem);
        $fileFetcher->fetch('1/2/3/123_file.txt', $filesystem)->willReturn($rawFile);

        $this->export('1/2/3/123_file.txt', sys_get_temp_dir() . '/spec/export/file.txt', 'storage');

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

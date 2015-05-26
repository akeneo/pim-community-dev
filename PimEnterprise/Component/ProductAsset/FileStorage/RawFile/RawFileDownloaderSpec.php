<?php

namespace spec\PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use Prophecy\Argument;

class RawFileDownloaderSpec extends ObjectBehavior
{
    function it_downloads_a_file(FileInterface $file, FilesystemInterface $filesystem)
    {
        $file->getPathname()->willReturn('path/to/file.txt');

        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->shouldBeCalled();

        $rawFile = $this->download($file, $filesystem);
        $rawFile->shouldBeAnInstanceOf('\SplFileInfo');
        $rawPathname = $rawFile->getWrappedObject()->getPathname();

        unlink($rawPathname);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(
        FileInterface $file,
        FilesystemInterface $filesystem
    ) {
        $file->getPathname()->willReturn('path/to/file.txt');

        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The file "path/to/file.txt" is not present on the filesystem.')
        )->during('download', [$file, $filesystem]);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(
        FileInterface $file,
        FilesystemInterface $filesystem
    ) {
        $file->getPathname()->willReturn('path/to/file.txt');

        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException('Unable to download the file "path/to/file.txt" from the filesystem.')
        )->during('download', [$file, $filesystem]);
    }
}

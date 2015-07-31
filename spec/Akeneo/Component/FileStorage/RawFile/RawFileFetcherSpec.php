<?php

namespace spec\Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RawFileFetcherSpec extends ObjectBehavior
{
    function it_fetches_a_file(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->shouldBeCalled();

        $rawFile = $this->fetch('path/to/file.txt', $filesystem);
        $rawFile->shouldBeAnInstanceOf('\SplFileInfo');
        $rawPathname = $rawFile->getWrappedObject()->getPathname();

        unlink($rawPathname);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The file "path/to/file.txt" is not present on the filesystem.')
        )->during('fetch', ['path/to/file.txt', $filesystem]);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.')
        )->during('fetch', ['path/to/file.txt', $filesystem]);
    }
}

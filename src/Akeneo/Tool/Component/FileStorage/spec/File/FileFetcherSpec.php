<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;

class FileFetcherSpec extends ObjectBehavior
{
    function it_fetches_a_file(Local $adapter, FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->shouldBeCalled();

        $rawFile = $this->fetch($filesystem, 'path/to/file.txt');
        $rawFile->shouldBeAnInstanceOf('\SplFileInfo');
        $rawPathname = $rawFile->getWrappedObject()->getPathname();

        unlink($rawPathname);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The file "path/to/file.txt" is not present on the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt']);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt']);
    }
}

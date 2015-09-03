<?php

namespace spec\Akeneo\Component\FileStorage\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileFetcherSpec extends ObjectBehavior
{
    function let(Filesystem $tmpFilesystem)
    {
        $this->beConstructedWith($tmpFilesystem);
    }

    function it_fetches_a_file($tmpFilesystem, Local $adapter, FilesystemInterface $filesystem)
    {
        if (!is_dir(sys_get_temp_dir() . '/spec/path/to')) {
            mkdir(sys_get_temp_dir() . '/spec/path/to', 0777, true);
        }

        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->shouldBeCalled();

        $tmpFilesystem->getAdapter()->willReturn($adapter);
        $adapter->getPathPrefix()->willReturn(sys_get_temp_dir() . '/spec/');

        $tmpFilesystem->has('path/to')->willReturn(false);
        $tmpFilesystem->createDir('path/to')->shouldBeCalled();

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

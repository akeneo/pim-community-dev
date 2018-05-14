<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Exception\Prediction\FailedPredictionException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class StreamedFileFetcherSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $directory;

    function let()
    {
        $this->directory = sys_get_temp_dir() . '/spec/';

        $this->filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $this->filesystem->mkdir($this->directory);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_fetches_a_file(FilesystemInterface $filesystem)
    {
        $virtualFilesystemPath = $this->directory . 'my file.txt';
        touch($virtualFilesystemPath);

        $fp = fopen($virtualFilesystemPath, 'r');
        $filesystem->has($virtualFilesystemPath)->willReturn(true);
        $filesystem->readStream($virtualFilesystemPath)->willReturn($fp);

        $this->fetch($filesystem, $virtualFilesystemPath, [])
            ->shouldBeAnInstanceOf(StreamedFileResponse::class);

        fclose($fp);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileNotFoundException('path/to/file.txt')
        )->during('fetch', [$filesystem, 'path/to/file.txt', []]);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt', []]);
    }
}

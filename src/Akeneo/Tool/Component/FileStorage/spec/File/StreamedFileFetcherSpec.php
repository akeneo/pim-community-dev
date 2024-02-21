<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;
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

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_fetches_a_file(FilesystemReader $filesystem)
    {
        $virtualFilesystemPath = $this->directory . 'my file.txt';
        touch($virtualFilesystemPath);

        $fp = fopen($virtualFilesystemPath, 'r');
        $filesystem->fileExists($virtualFilesystemPath)->willReturn(true);
        $filesystem->readStream($virtualFilesystemPath)->willReturn($fp);

        $this->fetch($filesystem, $virtualFilesystemPath, [])
            ->shouldBeAnInstanceOf(StreamedFileResponse::class);

        fclose($fp);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(FilesystemReader $filesystem)
    {
        $filesystem->fileExists('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileNotFoundException('path/to/file.txt')
        )->during('fetch', [$filesystem, 'path/to/file.txt', []]);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(FilesystemReader $filesystem)
    {
        $filesystem->fileExists('path/to/file.txt')->willReturn(true);
        $e = UnableToReadFile::fromLocation('path/to/file.txt');
        $filesystem->readStream('path/to/file.txt')->willThrow($e);

        $this->shouldThrow(
            new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt', []]);
    }
}

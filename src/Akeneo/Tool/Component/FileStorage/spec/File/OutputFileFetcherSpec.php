<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Exception\Prediction\FailedPredictionException;

class OutputFileFetcherSpec extends ObjectBehavior
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
        $virtualFilesystemPath = 'virtual/path/file.txt';
        $localFilesystemPath = [
            'filePath' => $this->directory . 'locale/path/',
            'filename' => 'filename.txt'
        ];

        $filesystem->has($virtualFilesystemPath)->willReturn(true);
        $filesystem->readStream($virtualFilesystemPath)->shouldBeCalled();

        $this->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath)->shouldBeAnInstanceOf('\SplFileInfo');

        if (!file_exists($localFilesystemPath['filePath'] . $localFilesystemPath['filename'])) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been created', $localFilesystemPath['filename'])
            );
        }
    }

    function it_fetches_a_file_with_the_same_filename(FilesystemInterface $filesystem)
    {
        $virtualFilesystemPath = 'virtual/path/file.txt';
        $localFilesystemPath = [
            'filePath' => $this->directory . 'locale/path/'
        ];

        $filesystem->has($virtualFilesystemPath)->willReturn(true);
        $filesystem->readStream($virtualFilesystemPath)->shouldBeCalled();

        $this->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath)->shouldBeAnInstanceOf('\SplFileInfo');

        if (!file_exists($localFilesystemPath['filePath'] . 'file.txt')) {
            throw new FailedPredictionException('File file.txt" should have been created');
        }
    }

    function it_throws_an_exception_if_options_directory_or_filename_are_not_filled(FilesystemInterface $filesystem)
    {
        $this->shouldThrow(
            new \LogicException('Options "filePath" has to be filled')
        )->during('fetch', [$filesystem, 'path/to/file.txt']);

        $this->shouldThrow(
            new \LogicException('Options "filePath" has to be filled')
        )->during('fetch', [$filesystem, 'path/to/file.txt', [
            'filePath' => ''
        ]]);

        $this->shouldThrow(
            new \LogicException('Options "filePath" has to be filled')
        )->during('fetch', [$filesystem, 'path/to/file.txt', [
            'filePath' => null
        ]]);
    }

    function it_throws_an_exception_when_the_file_is_not_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The file "path/to/file.txt" is not present on the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt', [
            'filePath' => 'locale/path/filename.txt'
        ]]);
    }

    function it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(FilesystemInterface $filesystem)
    {
        $filesystem->has('path/to/file.txt')->willReturn(true);
        $filesystem->readStream('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.')
        )->during('fetch', [$filesystem, 'path/to/file.txt', [
            'filePath' => 'locale/path/filename.txt'
        ]]);
    }
}

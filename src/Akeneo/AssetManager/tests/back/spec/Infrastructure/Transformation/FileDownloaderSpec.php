<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;

class FileDownloaderSpec extends ObjectBehavior
{
    function let(
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $storageFilesystem
    ) {
        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($storageFilesystem);
        $this->beConstructedWith($filesystemProvider, $fileFetcher);

    }

    function it_is_a_file_downloader()
    {
        $this->shouldHaveType(FileDownloader::class);
    }

    function it_downloads_a_file_and_returns_the_downladed_file_path(
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $storageFilesystem,
        \SplFileInfo $fileInfo
    ) {
        $fileInfo->getPathname()->willReturn('/destination/dir/jambon.png');
        $fileFetcher->fetch(
            $storageFilesystem,
            'key_for_the_wanted_file',
            [
                'filePath' => '/destination/dir',
                'filename' => 'jambon.png',
            ]
        )->willReturn($fileInfo);

        $this->get('key_for_the_wanted_file', '/destination/dir', 'jambon.png')
             ->shouldReturn('/destination/dir/jambon.png');
    }
}

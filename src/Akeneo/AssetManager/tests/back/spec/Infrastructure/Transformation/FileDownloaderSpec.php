<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class FileDownloaderSpec extends ObjectBehavior
{
    function let(
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $tmpFilesystem
    ) {
        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($tmpFilesystem);
        $this->beConstructedWith($filesystemProvider, $fileFetcher);

    }

    function it_is_a_file_downloader()
    {
        $this->shouldHaveType(FileDownloader::class);
    }

    function it_downloads_a_file(
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $tmpFilesystem,
        \SplFileInfo $fileInfo
    ) {
        $fileInfo->getPathname()->willReturn('/some/path/myfile.png');
        $fileFetcher->fetch($tmpFilesystem, 'key_for_the_wanted_file')->willReturn($fileInfo);

        $downloadedFile = $this->get('key_for_the_wanted_file');

        $downloadedFile->shouldBeAnInstanceOf(File::class);
        $downloadedFile->getPathname()->shouldReturn('/some/path/myfile.png');
    }
}

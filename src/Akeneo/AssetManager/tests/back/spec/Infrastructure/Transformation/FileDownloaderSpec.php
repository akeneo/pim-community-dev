<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class FileDownloaderSpec extends ObjectBehavior
{
    function let(
        FilesystemProvider $filesystemProvider,
        TemporaryFileFactory $temporaryFileFactory
    ) {
        $this->beConstructedWith($filesystemProvider, $temporaryFileFactory);
        $this->shouldHaveType(FileDownloader::class);
    }

    function it_downloads_the_file(
        FilesystemProvider $filesystemProvider,
        TemporaryFileFactory $temporaryFileFactory,
        FilesystemInterface $filesystem
    ) {
        $file = new File(__DIR__ . '/Operation/akeneo.png');
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->has('/path/to/file')->willReturn(true);
        $filesystem->read('/path/to/file')->willReturn('fileContent');
        $temporaryFileFactory->createFromContent('fileContent')->shouldBeCalledOnce()->willReturn($file);

        $this->get('/path/to/file')->shouldReturn($file);
    }

    function it_throws_an_exception_when_file_does_not_exist(
        FilesystemProvider $filesystemProvider,
        TemporaryFileFactory $temporaryFileFactory,
        FilesystemInterface $filesystem
    ) {
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->has('/path/to/file')->willReturn(false);

        $this
            ->shouldThrow(new MediaFileNotFoundException('The file "/path/to/file" can not be found.'))
            ->during('get', ['/path/to/file']);
    }

    function it_throws_an_exception_when_file_can_not_be_downloaded(
        FilesystemProvider $filesystemProvider,
        TemporaryFileFactory $temporaryFileFactory,
        FilesystemInterface $filesystem
    ) {
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->has('/path/to/file')->willReturn(true);
        $filesystem->read('/path/to/file')->willReturn(false);

        $this
            ->shouldThrow(new MediaFileNotFoundException('The file "/path/to/file" can not be downloaded.'))
            ->during('get', ['/path/to/file']);
    }
}

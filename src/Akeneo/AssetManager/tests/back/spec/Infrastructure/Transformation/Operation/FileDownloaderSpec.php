<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Infrastructure\Transformation\Operation\FileDownloader;
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
        $file = new File(__DIR__ . '/akeneo.png');
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->has('/path/to/file')->willReturn(true);
        $filesystem->read('/path/to/file')->willReturn('fileContent');
        $temporaryFileFactory->createFromContent('fileContent')->shouldBeCalledOnce()->willReturn($file);

        $result = $this->get('/path/to/file')->shouldReturn($file);
    }
}

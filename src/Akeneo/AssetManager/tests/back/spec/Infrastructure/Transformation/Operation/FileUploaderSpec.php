<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Infrastructure\Transformation\Operation\FileUploader;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class FileUploaderSpec extends ObjectBehavior
{
    function let(FilesystemProvider $filesystemProvider)
    {
        $this->beConstructedWith($filesystemProvider);
        $this->shouldHaveType(FileUploader::class);
    }

    function it_uploads_a_file(
        FilesystemProvider $filesystemProvider,
        Filesystem $filesystem
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->put('/path/to/file', file_get_contents(__DIR__ . '/akeneo.png'))->willReturn(true);

        $this->put($file, '/path/to/file');
    }
}

<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Infrastructure\Transformation\FileUploader;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
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
        $file = new File(__DIR__ . '/Operation/akeneo.png');
        $filesystemProvider->getFilesystem('assetManagerStorage')->willReturn($filesystem);
        $filesystem->put('/path/to/file', file_get_contents(__DIR__ . '/Operation/akeneo.png'))->willReturn(true);

        $this->put($file, '/path/to/file');
    }
}

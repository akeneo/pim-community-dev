<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class FileDownloaderSpec extends ObjectBehavior
{
    function let(
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        Filesystem $filesystem,
        FilesystemInterface $storageFilesystem
    ) {
        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($storageFilesystem);
        $this->beConstructedWith($filesystemProvider, $fileFetcher, $filesystem);
    }

    function it_is_a_file_downloader()
    {
        $this->shouldHaveType(FileDownloader::class);
    }

    function it_downloads_a_file_and_returns_a_copy_of_the_downloaded_file(
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $storageFilesystem,
        Filesystem $filesystem
    ) {
        $fileFetcher->fetch(
            $storageFilesystem,
            'key_for_the_wanted_file',
            Argument::cetera()
        )->shouldBeCalledOnce()->willReturn(new \SplFileInfo('/destination/dir/123456abcdef'));

        $filesystem->copy('/destination/dir/123456abcdef', '/destination/dir/jambon.png', true)->shouldBeCalled();

        $this->get('key_for_the_wanted_file', '/destination/dir', 'jambon.png')
             ->shouldBeLike(new File('/destination/dir/jambon.png', false));
    }

    function it_does_not_download_a_file_if_it_is_cached(
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $storageFilesystem,
        Filesystem $filesystem
    ) {
        $fileFetcher->fetch(
            $storageFilesystem,
            'key_for_the_wanted_file',
            Argument::cetera()
        )->shouldBeCalledOnce()->willReturn(new \SplFileInfo('/destination/dir/123456abcdef'));

        $filesystem->copy('/destination/dir/123456abcdef', '/destination/dir/jambon.png', true)->shouldBeCalled();

        $this->get('key_for_the_wanted_file', '/destination/dir', 'jambon.png')
             ->shouldBeLike(new File('/destination/dir/jambon.png', false));

        $filesystem->copy('/destination/dir/123456abcdef', '/other/dir/anothername.png', true)->shouldBeCalled();
        $this->get('key_for_the_wanted_file', '/other/dir', 'anothername.png')
             ->shouldBeLike(new File('/other/dir/anothername.png', false));
    }

    function it_deletes_old_files_when_there_are_more_than_limit(
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $storageFilesystem,
        Filesystem $filesystem
    ) {
        $fileFetcher->fetch($storageFilesystem, Argument::cetera())->shouldBeCalledTimes(11)->will(
            fn($arguments) => new \SplFileInfo($arguments[2]['filePath'] . '/' . $arguments[1])
        );
        $filesystem->copy(Argument::cetera())->shouldBeCalledTimes(11);

        $filesystem->exists('/destination/dir/my_file_key_1')->shouldBeCalledOnce()->willReturn(true);
        $filesystem->remove('/destination/dir/my_file_key_1')->shouldBeCalledOnce();

        for ($i = 1; $i <= 11; $i++) {
            $key = 'my_file_key_' . (string)$i;
            $this->get($key, '/destination/dir', 'jambon.png');
        }
    }
}

<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;

class FileStorerSpec extends ObjectBehavior
{
    function let(
        FilesystemProvider $filesystemProvider,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory,
        FileInfoInterface $fileInfo,
        FilesystemOperator $filesystem
    ) {
        $fileInfo->getKey()->willReturn('a/b/c/image.png');
        $fileInfo->getMimeType()->willReturn('image/png');
        $factory->createFromRawFile(Argument::type(\SplFileInfo::class), Argument::type('string'))
            ->willReturn($fileInfo);
        $filesystemProvider->getFilesystem(Argument::type('string'))->willReturn($filesystem);
        $this->beConstructedWith($filesystemProvider, $saver, $factory);
    }

    function it_stores_a_raw_file(
        SaverInterface $saver,
        FileInfoInterface $fileInfo,
        FilesystemOperator $filesystem,
        \SplFileInfo $rawFile
    ) {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);

        $filesystem->fileExists('a/b/c/image.png')->willReturn(false);
        $filesystem->writeStream('a/b/c/image.png', Argument::cetera())->shouldBeCalled();

        $saver->save($fileInfo)->shouldBeCalled();
        $this->store($rawFile, 'destination');

        if (!file_exists($localPathname)) {
            throw new FailedPredictionException(sprintf('File "%s" should not have been deleted.', $localPathname));
        }

        unlink($localPathname);
    }

    function it_stores_a_raw_file_and_deletes_it_locally(
        SaverInterface $saver,
        FileInfoInterface $fileInfo,
        FilesystemOperator $filesystem,
        \SplFileInfo $rawFile
    ) {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);

        $filesystem->fileExists('a/b/c/image.png')->willReturn(false);
        $filesystem->writeStream('a/b/c/image.png', Argument::cetera())->shouldBeCalled();

        $saver->save($fileInfo)->shouldBeCalled();
        $this->store($rawFile, 'destination', true);

        if (file_exists($localPathname)) {
            throw new FailedPredictionException(sprintf('File "%s" should have been deleted.', $localPathname));
        }
    }

    function it_throws_an_exception_if_the_file_can_not_be_writen_on_the_filesystem(
        SaverInterface $saver,
        FilesystemOperator $filesystem,
        \SplFileInfo $rawFile
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);

        $filesystem->fileExists('a/b/c/image.png')->willReturn(false);
        $filesystem->writeStream('a/b/c/image.png', Argument::cetera())->willThrow(
            UnableToWriteFile::atLocation(__FILE__, 'Directory is not writable')
        );

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
    }

    function it_throws_an_exception_if_the_file_already_exists_on_the_filesystem(
        SaverInterface $saver,
        FilesystemOperator $filesystem,
        \SplFileInfo $rawFile
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);

        $filesystem->fileExists(Argument::any())->willReturn(true);
        $filesystem->writeStream(Argument::cetera())->shouldNotBeCalled();
        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
    }

    function it_throws_an_exception_if_the_input_file_is_invalid()
    {
        $rawFile = new \SplFileInfo('/that/does/not/exist.jpg');

        $this->shouldThrow(InvalidFile::class)->during('store', [$rawFile, 'destination']);
    }
}

class CustomFileInfo extends \SplFileInfo
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }
}

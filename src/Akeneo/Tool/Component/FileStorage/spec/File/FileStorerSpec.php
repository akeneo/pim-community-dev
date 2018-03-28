<?php

namespace spec\Akeneo\Component\FileStorage\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;

class FileStorerSpec extends ObjectBehavior
{
    function let(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory
    ) {
        $this->beConstructedWith($mountManager, $saver, $factory);
    }

    function it_stores_a_raw_file(
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInfoInterface $fileInfo
    ) {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);
        $fs->has(Argument::any())->willReturn(false);

        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($fileInfo);

        $fs->writeStream(Argument::cetera())->shouldBeCalled();

        $saver->save($fileInfo)->shouldBeCalled();
        $this->store($rawFile, 'destination');

        if (!file_exists($localPathname)) {
            throw new FailedPredictionException(sprintf('File "%s" should not have been deleted.', $localPathname));
        }

        unlink($localPathname);
    }

    function it_stores_a_raw_file_and_deletes_it_locally(
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInfoInterface $fileInfo
    ) {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);
        $fs->has(Argument::any())->willReturn(false);

        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($fileInfo);

        $fs->writeStream(Argument::cetera())->shouldBeCalled();

        $saver->save($fileInfo)->shouldBeCalled();
        $this->store($rawFile, 'destination', true);

        if (file_exists($localPathname)) {
            throw new FailedPredictionException(sprintf('File "%s" should have been deleted.', $localPathname));
        }
    }

    function it_throws_an_exception_if_the_file_can_not_be_writen_on_the_filesystem(
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInfoInterface $fileInfo
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($fileInfo);
        $fs->writeStream(Argument::cetera())->willReturn(false);

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
    }

    function it_throws_an_exception_if_the_file_already_exists_on_the_filesystem(
        $mountManager,
        $factory,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInfoInterface $fileInfo
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $fs->has(Argument::any())->willReturn(true);
        $fs->writeStream(Argument::cetera())->willThrow(new FileExistsException('The file exists.'));
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('key-file');
        $fileInfo->getMimeType()->willReturn('mime-type');

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
    }
}

class CustomFileInfo extends \SplFileInfo
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }
}

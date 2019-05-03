<?php

namespace spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
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
        $saver
    ) {
        $fileToStore = new \SplFileInfo('path/to/file.png');
        $newFileInfo = new FileInfo();
        $newFileInfo->setKey('path/to/file.png');
        $factory->createFromRawFile($fileToStore, 'pefTmpStorage')->willReturn($newFileInfo);

        $mountManager->move(
            'pefTmpStorage://path/to/file.png',
            'destinationStorage://path/to/file.png'
        )->willReturn(true);

        $saver->save($newFileInfo)->shouldBeCalled();

        $this->store($fileToStore, 'destinationStorage');
    }

    function it_throws_an_exception_if_the_file_can_not_be_writen_on_the_filesystem(
        $mountManager,
        $factory,
        $saver
    ) {
        $fileToStore = new \SplFileInfo('path/to/file.png');
        $newFileInfo = new FileInfo();
        $newFileInfo->setKey('path/to/file.png');
        $factory->createFromRawFile($fileToStore, 'pefTmpStorage')->willReturn($newFileInfo);

        $mountManager->move(
            'pefTmpStorage://path/to/file.png',
            'destinationStorage://path/to/file.png'
        )->willReturn(false);

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(FileTransferException::class)
            ->during('store', [$fileToStore, 'destinationStorage']);
    }

    function it_throws_an_exception_if_the_file_already_exists_on_the_filesystem(
        $mountManager,
        $factory,
        $saver
    ) {
        $fileToStore = new \SplFileInfo('path/to/file.png');
        $newFileInfo = new FileInfo();
        $newFileInfo->setKey('path/to/file.png');
        $factory->createFromRawFile($fileToStore, 'pefTmpStorage')->willReturn($newFileInfo);

        $mountManager->move(
            'pefTmpStorage://path/to/file.png',
            'destinationStorage://path/to/file.png'
        )->willThrow(FileExistsException::class);

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(FileTransferException::class)
            ->during('store', [$fileToStore, 'destinationStorage']);
    }
}


<?php

namespace spec\Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\FileFactoryInterface;
use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;

class RawFileStorerSpec extends ObjectBehavior
{
    function let(
        MountManager $mountManager,
        SaverInterface $saver,
        FileFactoryInterface $factory
    ) {
        $this->beConstructedWith($mountManager, $saver, $factory);
    }

    function it_stores_a_raw_file(
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInterface $file
    ) {
        $localPathname = __DIR__ . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);
        $fs->has(Argument::any())->willReturn(false);

        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($file);

        $fs->writeStream(Argument::any(), Argument::any())->shouldBeCalled();

        $saver->save($file, ['flush_only_object' => true])->shouldBeCalled();
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
        FileInterface $file
    ) {
        $localPathname = __DIR__ . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);
        $fs->has(Argument::any())->willReturn(false);

        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($file);

        $fs->writeStream(Argument::any(), Argument::any())->shouldBeCalled();

        $saver->save($file, ['flush_only_object' => true])->shouldBeCalled();
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
        FileInterface $file
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($file);
        $fs->writeStream(Argument::any(), Argument::any())->willReturn(false);

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
        FileInterface $file
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $fs->has(Argument::any())->willReturn(true);
        $fs->writeStream(Argument::any(), Argument::any())->willThrow(new FileExistsException('The file exists.'));
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $factory->createFromRawFile($rawFile, 'destination')->willReturn($file);
        $file->getKey()->willReturn('key-file');

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

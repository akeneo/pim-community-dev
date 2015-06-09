<?php

namespace spec\Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use Prophecy\Argument;

class FileMoverSpec extends ObjectBehavior
{
    function let(MountManager $mountManager, SaverInterface $saver)
    {
        $this->beConstructedWith($mountManager, $saver);
    }

    public function it_moves_a_file_from_a_vfs_to_another($mountManager, $saver, FileInterface $file)
    {
        $file->getKey()->willReturn('path/to/file.txt');

        $mountManager->move('source://path/to/file.txt', 'destination://path/to/file.txt')->willReturn(true);
        $file->setStorage('destination')->shouldBeCalled();
        $saver->save($file)->shouldBeCalled();

        $this->move($file, 'source', 'destination');
    }

    public function it_throws_an_exception_if_the_file_can_not_be_moved($mountManager, $saver, FileInterface $file)
    {
        $file->getKey()->willReturn('path/to/file.txt');

        $mountManager->move('source://path/to/file.txt', 'destination://path/to/file.txt')->willReturn(false);
        $file->setStorage(Argument::any())->shouldNotBeCalled();
        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new FileTransferException('Impossible to move the file "path/to/file.txt" from "source" to "destination".')
        )->during(
            'move',
            [$file, 'source', 'destination']
        );
    }
}

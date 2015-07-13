<?php

namespace spec\Akeneo\Component\FileStorage\VirtualFileSystem;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\Model\FileInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileCopierSpec extends ObjectBehavior
{
    function let(MountManager $mountManager)
    {
        $this->beConstructedWith($mountManager);
    }

    function it_copies_a_file_from_a_vfs_to_another($mountManager)
    {
        $mountManager->copy('source://path/to/file.txt', 'destination://path/to/file.txt')->willReturn(true);

        $this->copy('source', 'path/to/file.txt', 'destination');
    }

    function it_copies_a_file_from_a_vfs_to_another_with_a_special_pathname($mountManager)
    {
        $mountManager->copy('source://path/to/file.txt', 'destination://special/path/to/copy/file.txt')->willReturn(true);

        $this->copy('source', 'path/to/file.txt', 'destination', 'special/path/to/copy/file.txt');
    }

    function it_throws_an_exception_if_the_file_can_not_be_copied($mountManager, FileInterface $file)
    {
        $file->getKey()->willReturn('path/to/file.txt');

        $mountManager->copy('source://path/to/file.txt', 'destination://path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new FileTransferException(
                'Impossible to copy the file from "source://path/to/file.txt" to "destination://path/to/file.txt".'
            )
        )->during(
            'copy',
            ['source', 'path/to/file.txt', 'destination']
        );
    }
}

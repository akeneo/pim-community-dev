<?php

namespace spec\Akeneo\Tool\Component\FileStorage;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;

class FilesystemProviderSpec extends ObjectBehavior
{
    function let(MountManager $mountManager)
    {
        $this->beConstructedWith($mountManager);
    }

    function it_gets_the_filesystem($mountManager, FilesystemInterface $filesystem)
    {
        $mountManager->getFilesystem('foo')->willReturn($filesystem);

        $this->getFilesystem('foo')->shouldReturn($filesystem);
    }

    function it_throws_an_exception_when_the_filesystem_does_not_exist($mountManager)
    {
        $mountManager->getFilesystem('foo')->willThrow(new \LogicException());

        $this->shouldThrow('\LogicException')->during('getFilesystem', ['foo']);
    }
}

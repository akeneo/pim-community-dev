<?php

namespace spec\Akeneo\Tool\Component\FileStorage;

use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;

class FilesystemProviderSpec extends ObjectBehavior
{
    function let(FilesystemOperator $filesystem1, FilesystemOperator $filesystem2)
    {
        $this->beConstructedWith(
            [
                'foo' => $filesystem1,
                'bar' => $filesystem2,
            ]
        );
    }

    function it_gets_the_filesystem(FilesystemOperator $filesystem1, FilesystemOperator $filesystem2)
    {
        $this->getFilesystem('foo')->shouldReturn($filesystem1);
        $this->getFilesystem('bar')->shouldReturn($filesystem2);
    }

    function it_throws_an_exception_when_the_filesystem_does_not_exist()
    {
        $this->shouldThrow(\LogicException::class)->during('getFilesystem', ['baz']);
    }
}

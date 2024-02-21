<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use PhpSpec\ObjectBehavior;

class WrittenFileInfoSpec extends ObjectBehavior
{
    function it_cannot_be_instantiated_with_an_empty_key()
    {
        $this->beConstructedThrough('fromFileStorage', ['', 'catalogStorage', 'files/media.png']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_empty_storage()
    {
        $this->beConstructedThrough('fromFileStorage', ['a/b/c/media.png', '', 'files/media.png']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_empty_filepath()
    {
        $this->beConstructedThrough('fromFileStorage', ['a/b/c/media.png', 'catalogStorage', '']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_describe_a_remote_storage_file()
    {
        $this->beConstructedThrough('fromFileStorage', ['a/b/c/media.png', 'catalogStorage', 'files/media.png']);
        $this->shouldHaveType(WrittenFileInfo::class);
        $this->isLocalFile()->shouldBe(false);
    }

    function it_can_describe_a_local_file()
    {
        $this->beConstructedThrough('fromLocalFile', ['a/b/c/media.png', 'files/media.png']);
        $this->shouldHaveType(WrittenFileInfo::class);
        $this->isLocalFile()->shouldBe(true);
    }
}

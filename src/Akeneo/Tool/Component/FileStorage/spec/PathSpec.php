<?php

namespace spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\Path;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('storage', '/my/', 'path/', 'to/file.png');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Path::class);
    }

    function it_is_built_without_any_storage()
    {
        $this::withoutStorage('my', 'path/', 'to')->shouldBeLike(new Path('',  'my', 'path/', 'to'));
    }

    function it_displays_the_path_with_its_storage()
    {
        $this->__toString()->shouldReturn('storage://my/path/to/file.png');
    }

    function it_displays_the_path_without_any_storage()
    {
        $this->beConstructedThrough('withoutStorage', ['/my/', 'path/', 'to/file.png']);
        $this->__toString()->shouldReturn('my/path/to/file.png');
    }
}

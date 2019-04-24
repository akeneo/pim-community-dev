<?php

namespace spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\Path;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('my', 'path/', 'to');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Path::class);
    }

    function it_displays_the_path()
    {
        $this->__toString()->shouldReturn('my/path/to');
    }
}

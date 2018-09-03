<?php

namespace spec\Akeneo\Test\Common;

use Akeneo\Test\Common\Path;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('tests', 'back');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Path::class);
    }

    function it_has_a_relative_path()
    {
        $this->relativePath()->shouldReturn('tests/back');
    }

    function it_has_a_root_path()
    {
        $this->relativePath()->shouldEndWith('tests/back');
    }

    function it_has_an_absolute_path()
    {
        $this->absolutePath()->shouldEndWith('tests/back');
    }
}

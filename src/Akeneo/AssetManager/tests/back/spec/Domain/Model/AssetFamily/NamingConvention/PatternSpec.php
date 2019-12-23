<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Pattern;
use PhpSpec\ObjectBehavior;

class PatternSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Pattern::class);
    }

    function it_is_a_pattern_value_object()
    {
        $this->beConstructedThrough('create', ['pattern']);
        $this->shouldBeAnInstanceOf(Pattern::class);
    }

    function it_can_be_normalized()
    {
        $this->beConstructedThrough('create', ['pattern']);
        $this->normalize()->shouldReturn('pattern');
    }
}

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
        $this->beConstructedThrough('create', ['/(\w+)-ref/']);
        $this->shouldBeAnInstanceOf(Pattern::class);
    }

    function it_represents_a_valid_regular_expression_pattern()
    {
        $this->beConstructedThrough('create', ['/invalid_regexp(]']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->beConstructedThrough('create', ['/(.*)/']);
        $this->normalize()->shouldReturn('/(.*)/');
    }
}

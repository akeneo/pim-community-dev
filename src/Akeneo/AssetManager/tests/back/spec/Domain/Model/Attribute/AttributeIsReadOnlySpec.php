<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use PhpSpec\ObjectBehavior;

class AttributeIsReadOnlySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsReadOnly::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->normalize()->shouldReturn(true);
    }
}

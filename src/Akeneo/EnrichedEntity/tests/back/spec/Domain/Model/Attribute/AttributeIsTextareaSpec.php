<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use PhpSpec\ObjectBehavior;

class AttributeIsTextareaSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsTextarea::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->isYes()->shouldReturn(true);
        $this::fromBoolean(false)->isYes()->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(true);
    }
}

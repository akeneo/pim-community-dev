<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextArea;
use PhpSpec\ObjectBehavior;

class AttributeIsTextAreaSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsTextArea::class);
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

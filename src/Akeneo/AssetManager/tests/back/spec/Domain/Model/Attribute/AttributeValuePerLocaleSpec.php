<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use PhpSpec\ObjectBehavior;

class AttributeValuePerLocaleSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeValuePerLocale::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(true);
    }

    function it_tells_if_it_is_true()
    {
        $this->isTrue()->shouldReturn(true);
    }
}

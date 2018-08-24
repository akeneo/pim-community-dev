<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegex;
use PhpSpec\ObjectBehavior;

class AttributeRegexSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['/\w+/']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRegex::class);
    }

    function it_can_be_created_with_no_regex()
    {
        $noRegex = $this::NONE();
        $noRegex->normalize()->shouldReturn(null);
    }

    function it_says_if_it_holds_no_regex()
    {
        $this->isNone()->shouldReturn(false);
        $this::none()->isNone()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('/\w+/');
    }
}

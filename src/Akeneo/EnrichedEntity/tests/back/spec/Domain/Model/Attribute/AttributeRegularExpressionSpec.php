<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use PhpSpec\ObjectBehavior;

class AttributeRegularExpressionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['/\w+/']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRegularExpression::class);
    }

    function it_can_be_created_with_no_regular_expression()
    {
        $noRegex = $this::NONE();
        $noRegex->normalize()->shouldReturn(null);
    }

    function it_says_if_it_holds_no_regularExpression()
    {
        $this->isNone()->shouldReturn(false);
        $this::none()->isNone()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('/\w+/');
    }
}

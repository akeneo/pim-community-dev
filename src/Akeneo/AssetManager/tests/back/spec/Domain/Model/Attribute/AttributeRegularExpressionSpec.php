<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
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
        $noRegex = $this::createEmpty();
        $noRegex->normalize()->shouldReturn(null);
    }

    function it_says_if_it_holds_no_regularExpression()
    {
        $this->isEmpty()->shouldReturn(false);
        $this::createEmpty()->isEmpty()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('/\w+/');
    }

    function it_cannot_be_created_with_an_invalid_regular_expression()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromString', ['invalid_regular_expression']);
    }
}

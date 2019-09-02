<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use PhpSpec\ObjectBehavior;

class AttributeValidationRuleSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', [AttributeValidationRule::EMAIL]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeValidationRule::class);
    }

    function it_can_be_created_with_any_validation_rule_it_supports()
    {
        foreach (AttributeValidationRule::VALIDATION_RULE_TYPES as $validationRule)
        {
            $this->beConstructedThrough('fromString', [$validationRule]);
        }
    }

    function it_can_be_created_without_any_validation_rule()
    {
        $noValidationRule = $this::none();
        $noValidationRule->normalize()->shouldReturn('none');
    }

    function it_cannot_be_created_with_an_unspported_validation_rule()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['unsupported_validation_rule']);
    }

    function it_tells_if_it_is_none()
    {
        $this::none()->isNone()->shouldReturn(true);
        $this::none()->isRegularExpression()->shouldReturn(false);
    }

    function it_tells_if_it_is_regular_expression()
    {
        $this::fromString(AttributeValidationRule::REGULAR_EXPRESSION)->isRegularExpression()->shouldReturn(true);
        $this::fromString(AttributeValidationRule::REGULAR_EXPRESSION)->isNone()->shouldReturn(false);
    }

    function it_tells_if_it_is_email()
    {
        $this::fromString(AttributeValidationRule::EMAIL)->isEmail()->shouldReturn(true);
        $this::fromString(AttributeValidationRule::EMAIL)->isNone()->shouldReturn(false);
    }

    function it_tells_if_it_is_url()
    {
        $this::fromString(AttributeValidationRule::URL)->isUrl()->shouldReturn(true);
        $this::fromString(AttributeValidationRule::URL)->isNone()->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(AttributeValidationRule::EMAIL);
    }
}

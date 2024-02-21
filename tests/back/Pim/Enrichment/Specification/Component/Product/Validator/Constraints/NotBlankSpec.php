<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class NotBlankSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['attributeCode' => '']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotBlank::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_an_attribute_code()
    {
        $this->beConstructedWith(['attributeCode' => 'color']);
        $this->attributeCode->shouldBe('color');
    }

    function it_returns_required_options()
    {
        $this->getRequiredOptions()->shouldReturn(['attributeCode']);
    }
}

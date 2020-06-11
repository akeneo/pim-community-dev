<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class IsNumericSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsNumeric::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The {{ attribute }} attribute requires a number, and the submitted {{ value }} value is not.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_provides_attribute_code(): void
    {
        $this->beConstructedWith(['attributeCode' => 'weight']);
        $this->attributeCode->shouldBe('weight');
    }

    function it_provides_empty_string_if_no_attribute_code_has_been_provided(): void
    {
        $this->attributeCode->shouldBe('');
    }
}

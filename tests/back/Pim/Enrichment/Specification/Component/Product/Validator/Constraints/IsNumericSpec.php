<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class IsNumericSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IsNumeric::class);
    }

    public function it_is_a_validator_constraint(): void
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    public function it_provides_attribute_code(): void
    {
        $this->beConstructedWith(['attributeCode' => 'weight']);
        $this->attributeCode->shouldBe('weight');
    }

    public function it_provides_empty_string_if_no_attribute_code_has_been_provided(): void
    {
        $this->attributeCode->shouldBe('');
    }
}

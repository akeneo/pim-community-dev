<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use PhpSpec\ObjectBehavior;

class IsNumericSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsNumeric::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('This value should be a valid number.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

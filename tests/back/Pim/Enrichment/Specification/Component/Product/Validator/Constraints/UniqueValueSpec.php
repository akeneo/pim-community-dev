<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use PhpSpec\ObjectBehavior;

class UniqueValueSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValue::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe(
            'The value %value% is already set on another product for the unique attribute %attribute%'
        );
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

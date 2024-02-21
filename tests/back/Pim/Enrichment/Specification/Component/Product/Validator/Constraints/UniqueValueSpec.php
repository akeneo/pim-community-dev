<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class UniqueValueSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValue::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe(
            'The {{ attribute_code }} attribute can not have the same value more than once. The {{ value }} value is already set on another product.'
        );
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}

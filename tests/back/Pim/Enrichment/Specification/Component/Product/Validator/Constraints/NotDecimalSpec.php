<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use PhpSpec\ObjectBehavior;

class NotDecimalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotDecimal::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_message()
    {
        $this->message->shouldBe('This value should not be a decimal.');
    }
}

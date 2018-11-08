<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use PhpSpec\ObjectBehavior;

class BooleanSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Boolean::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('Property "%attribute%" expects a boolean as data, "%givenType%" given.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

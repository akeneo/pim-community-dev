<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use PhpSpec\ObjectBehavior;

class IsStringSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsString::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('Property "%attribute%" expects a string as data, "%givenType%" given.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

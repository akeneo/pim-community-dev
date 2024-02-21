<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class IsStringSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsString::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The %attribute% attribute requires a string, a %givenType% was detected.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}

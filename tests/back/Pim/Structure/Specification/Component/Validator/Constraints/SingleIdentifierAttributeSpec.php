<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\SingleIdentifierAttribute;
use PhpSpec\ObjectBehavior;

class SingleIdentifierAttributeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SingleIdentifierAttribute::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_message()
    {
        $this->message->shouldBe('An identifier attribute already exists.');
    }
}

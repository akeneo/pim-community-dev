<?php

namespace spec\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class SingleIdentifierAttributeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Structure\Component\Validator\Constraints\SingleIdentifierAttribute');
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

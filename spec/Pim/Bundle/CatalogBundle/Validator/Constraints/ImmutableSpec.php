<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class ImmutableSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\catalogBundle\validator\Constraints\Immutable');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_message()
    {
        $this->message->shouldBe('This property cannot be changed.');
    }

    function it_can_get_targets()
    {
        $this->getTargets()->shouldReturn('class');
    }
}

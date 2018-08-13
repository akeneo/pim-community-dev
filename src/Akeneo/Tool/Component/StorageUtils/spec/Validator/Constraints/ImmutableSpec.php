<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\Immutable;
use PhpSpec\ObjectBehavior;

class ImmutableSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Immutable::class);
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

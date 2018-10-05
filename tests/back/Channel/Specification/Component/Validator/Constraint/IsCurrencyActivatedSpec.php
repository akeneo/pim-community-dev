<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Validator\Constraint\IsCurrencyActivated;
use Symfony\Component\Validator\Constraint;

class IsCurrencyActivatedSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsCurrencyActivated::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The currency "%currency%" has to be activated.');
    }
}

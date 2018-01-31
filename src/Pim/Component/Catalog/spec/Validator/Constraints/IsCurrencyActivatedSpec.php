<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\Constraints\IsCurrencyActivated;
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

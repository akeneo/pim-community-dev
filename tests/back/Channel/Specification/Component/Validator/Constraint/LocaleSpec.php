<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Validator\Constraint\Locale;
use PhpSpec\ObjectBehavior;

class LocaleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Locale::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The locale "%locale%" does not exist.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

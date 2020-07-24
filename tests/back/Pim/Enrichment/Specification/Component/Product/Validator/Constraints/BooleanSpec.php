<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class BooleanSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Boolean::class);
    }

    function it_has_message()
    {
        $this->message
            ->shouldBe('The {{ attribute_code }} attribute requires a boolean value (true or false) as data, a {{ given_type }} was detected.');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}

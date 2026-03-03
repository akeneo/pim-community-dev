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
        $this->message->shouldBe('pim_catalog.constraint.boolean.boolean_value_is_required');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}

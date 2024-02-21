<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfigured;
use PhpSpec\ObjectBehavior;

class IsReferenceDataConfiguredSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsReferenceDataConfigured::class);
    }

    function it_has_message()
    {
        $this->message
            ->shouldBe('Reference data "%reference_data_name%" does not exist. Allowed values are: %references%');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }
}

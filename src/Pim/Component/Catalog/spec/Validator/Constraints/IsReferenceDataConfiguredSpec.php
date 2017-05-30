<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class IsReferenceDataConfiguredSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\IsReferenceDataConfigured');
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

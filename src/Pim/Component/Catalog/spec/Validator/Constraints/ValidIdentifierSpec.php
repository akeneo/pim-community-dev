<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class ValidIdentifierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\ValidIdentifier');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_is_a_property_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::PROPERTY_CONSTRAINT);
    }

    function it_is_validated_by_valid_identifier()
    {
        $this->validatedBy()->shouldReturn('valid_identifier');
    }
}

<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class ValidDateRangeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValidDateRange::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('The max date must be greater than the min date');
    }

    function it_has_an_invalid_date_message()
    {
        $this->invalidDateMessage->shouldBe('This date is not valid');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

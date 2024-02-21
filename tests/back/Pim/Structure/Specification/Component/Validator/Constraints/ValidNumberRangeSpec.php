<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRange;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class ValidNumberRangeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValidNumberRange::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('The max number must be greater than the min number');
    }

    function it_has_an_invalid_number_message()
    {
        $this->invalidNumberMessage->shouldBe('This number is not valid');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

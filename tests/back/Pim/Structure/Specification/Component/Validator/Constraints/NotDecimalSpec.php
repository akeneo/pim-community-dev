<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimal;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class NotDecimalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotDecimal::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('This value should not be a decimal.');
    }
}

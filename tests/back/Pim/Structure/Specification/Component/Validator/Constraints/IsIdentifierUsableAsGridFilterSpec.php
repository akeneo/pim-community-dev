<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilter;
use Symfony\Component\Validator\Constraint;

class IsIdentifierUsableAsGridFilterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsIdentifierUsableAsGridFilter::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('"%code%" is an identifier attribute, it must be usable as grid filter');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}

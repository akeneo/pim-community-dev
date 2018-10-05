<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Validator\Constraint\IsRootCategory;
use Symfony\Component\Validator\Constraint;

class IsRootCategorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsRootCategory::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The category "%category%" has to be a root category.');
    }
}

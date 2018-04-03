<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\Constraints\IsRootCategory;
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

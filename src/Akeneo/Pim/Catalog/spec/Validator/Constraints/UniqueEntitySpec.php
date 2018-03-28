<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Validator\Constraints\UniqueEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class UniqueEntitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueEntity::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_validated_by_a_specific_validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_product_validator');
    }

    function it_validates_a_class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

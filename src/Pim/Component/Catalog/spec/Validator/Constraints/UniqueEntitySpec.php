<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Validator\Constraints\UniqueEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class UniqueEntitySpec extends ObjectBehavior
{
    function it is initializable()
    {
        $this->shouldHaveType(UniqueEntity::class);
    }

    function it is a constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it is validated by a specific validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_product_validator');
    }

    function it validates a class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

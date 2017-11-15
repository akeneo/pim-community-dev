<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Composer\Semver\Constraint\Constraint;
use Pim\Component\Catalog\Validator\ConstraintsProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UniqueProductSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConstraintsProduct::class);
    }

    function it is a constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it is validated by a specific validator()
    {
        $this->validatedBy()->shouldReturn('pim_immutable_product_validator');
    }

    function it validates a class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

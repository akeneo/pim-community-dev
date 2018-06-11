<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints\Product;

use Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class UniqueProductEntitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductEntity::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_validated_by_a_specific_validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_product_validator_entity');
    }

    function it_validates_a_class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

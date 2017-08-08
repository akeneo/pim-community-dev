<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Validator\Constraints\HasARootProductModelAsParent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class HasARootProductModelAsParentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HasARootProductModelAsParent::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
    
    function it_is_validated_by_the_variant_axes_validator()
    {
        $this->validatedBy()->shouldReturn('pim_has_a_root_product_model_as_parent');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}

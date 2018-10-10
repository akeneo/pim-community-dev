<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxes;
use Symfony\Component\Validator\Constraint;

class ImmutableVariantAxesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableVariantAxes::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }

    function it_is_validated_by_the_immutable_variant_axes_validator()
    {
        $this->validatedBy()->shouldReturn('pim_immutable_variant_axes_validator');
    }
}

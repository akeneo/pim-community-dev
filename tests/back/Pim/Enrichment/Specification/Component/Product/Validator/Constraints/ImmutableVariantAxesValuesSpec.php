<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValues;
use Symfony\Component\Validator\Constraint;

class ImmutableVariantAxesValuesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableVariantAxesValues::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn('class');
    }

    function it_is_validated_by_the_immutable_variant_axis_values_validator()
    {
        $this->validatedBy()->shouldReturn('pim_immutable_variant_axis_values_validator');
    }
}

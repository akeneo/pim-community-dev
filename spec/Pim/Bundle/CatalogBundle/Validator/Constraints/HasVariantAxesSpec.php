<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class HasVariantAxesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\HasVariantAxes');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_message()
    {
        $this->message->shouldBe(
            'The product "%product%" is in the variant group "%variant%" but it misses the following axes: %axes%.'
        );
    }

    function it_is_validated_by_the_variant_axes_validator()
    {
        $this->validatedBy()->shouldReturn('pim_has_variant_axes_validator');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn('class');
    }
}

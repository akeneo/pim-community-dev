<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class UniqueVariantAxisSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantAxis');
    }

    function it_has_message()
    {
        $this->message->shouldBe('Group "%variant group%" already contains another product with values "%values%"');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_is_validated_by_the_unique_variant_axis_validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_variant_axis_validator');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn('class');
    }
}

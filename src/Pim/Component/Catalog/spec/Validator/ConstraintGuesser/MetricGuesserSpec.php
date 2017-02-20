<?php

namespace spec\Pim\Component\Catalog\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class MetricGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Component\Catalog\Validator\ConstraintGuesserInterface');
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_metric');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);

        $attribute->getAttributeType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_always_guess(AttributeInterface $attribute)
    {
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(2);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\ValidMetric');
        $constraint = $constraints[1];
        $constraint->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\IsNumeric');
    }
}

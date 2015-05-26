<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class BooleanGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_boolean');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_textarea');
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

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Boolean');
    }
}

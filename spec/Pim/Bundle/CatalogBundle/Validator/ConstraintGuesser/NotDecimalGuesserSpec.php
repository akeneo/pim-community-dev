<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class NotDecimalGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_metric');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_number');
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

    function it_guesses_not_decimal(AttributeInterface $attribute)
    {
        $attribute->isDecimalsAllowed()
            ->willReturn(false)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal');
    }

    function it_does_not_guess_decimal_allowed(AttributeInterface $attribute)
    {
        $attribute->isDecimalsAllowed()
            ->willReturn(true)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

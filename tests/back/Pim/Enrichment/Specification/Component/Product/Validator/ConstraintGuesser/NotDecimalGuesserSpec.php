<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class NotDecimalGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_metric');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_number');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);

        $attribute->getType()
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
        $constraint->shouldBeAnInstanceOf(NotDecimal::class);
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

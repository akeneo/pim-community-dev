<?php

namespace spec\Pim\Component\Catalog\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class PriceCollectionGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Component\Catalog\Validator\ConstraintGuesserInterface');
    }

    function let(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn(null);
        $attribute->getNumberMin()
            ->willReturn(null);
        $attribute->getNumberMax()
            ->willReturn(null);
        $attribute->getType()
            ->willReturn(null);
        $attribute->isDecimalsAllowed()
            ->willReturn(null);
        $attribute->isNegativeAllowed()
            ->willReturn(null);
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_price_collection');
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

    function it_guesses_aggregated_guessers_simple(AttributeInterface $attribute)
    {
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraintsAll = $constraints[0];
        $constraintsAll->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\All');
        $constraintsAll->constraints->shouldHaveCount(4);

        $constraintsAll->constraints[0]
            ->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Type');
        $constraintsAll->constraints[1]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\IsNumeric');
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\NotDecimal');
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Currency');
    }

    function it_guesses_aggregated_guessers_without_notDecimal(AttributeInterface $attribute)
    {
        $attribute->isDecimalsAllowed()
            ->willReturn(true);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraintsAll = $constraints[0];
        $constraintsAll->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\All');

        $constraintsAll->constraints->shouldHaveCount(3);

        $constraintsAll->constraints[0]
            ->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Type');
        $constraintsAll->constraints[1]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\IsNumeric');
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Currency');
    }

    function it_guesses_aggregated_guessers_with_range(AttributeInterface $attribute)
    {
        $attribute->getNumberMin()
            ->willReturn(5);
        $attribute->getNumberMax()
            ->willReturn(10);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraintsAll = $constraints[0];
        $constraintsAll->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\All');

        $constraintsAll->constraints->shouldHaveCount(5);

        $constraintsAll->constraints[0]
            ->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Type');
        $constraintsAll->constraints[1]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\IsNumeric');
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\NotDecimal');
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Range');
        $constraintsAll->constraints[4]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Currency');
    }

    function it_guesses_aggregated_guessers_with_range_without_notDecimal(AttributeInterface $attribute)
    {
        $attribute->getNumberMin()
            ->willReturn(5);
        $attribute->getNumberMax()
            ->willReturn(10);

        $attribute->isDecimalsAllowed()
            ->willReturn(true);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraintsAll = $constraints[0];
        $constraintsAll->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\All');

        $constraintsAll->constraints->shouldHaveCount(4);

        $constraintsAll->constraints[0]
            ->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Type');
        $constraintsAll->constraints[0]->type
            ->shouldBe('Pim\Component\Catalog\Model\ProductPriceInterface');
        $constraintsAll->constraints[1]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\IsNumeric');
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Range');
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\Currency');
    }
}

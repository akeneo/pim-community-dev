<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class PriceCollectionGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
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
            ->shouldBeAnInstanceOf(IsNumeric::class);
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf(NotDecimal::class);
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf(Currency::class);
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
            ->shouldBeAnInstanceOf(IsNumeric::class);
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf(Currency::class);
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
            ->shouldBeAnInstanceOf(IsNumeric::class);
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf(NotDecimal::class);
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf(Range::class);
        $constraintsAll->constraints[4]
            ->shouldBeAnInstanceOf(Currency::class);
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
            ->shouldBe(ProductPriceInterface::class);
        $constraintsAll->constraints[1]
            ->shouldBeAnInstanceOf(IsNumeric::class);
        $constraintsAll->constraints[2]
            ->shouldBeAnInstanceOf(Range::class);
        $constraintsAll->constraints[3]
            ->shouldBeAnInstanceOf(Currency::class);
    }
}

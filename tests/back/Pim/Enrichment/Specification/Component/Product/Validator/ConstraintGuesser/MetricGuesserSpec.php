<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class MetricGuesserSpec extends ObjectBehavior
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
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);

        $attribute->getType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_always_guess(AttributeInterface $attribute)
    {
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(2);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(ValidMetric::class);
        $constraint = $constraints[1];
        $constraint->shouldBeAnInstanceOf(IsNumeric::class);
    }
}

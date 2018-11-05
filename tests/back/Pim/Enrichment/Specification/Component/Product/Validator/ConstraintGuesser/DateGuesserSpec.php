<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\DateGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class DateGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DateGuesser::class);
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_supports_date_attributes(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_date');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);
    }

    function it_does_not_support_other_attributes(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_guesses_date(AttributeInterface $attribute)
    {
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Date');
    }
}

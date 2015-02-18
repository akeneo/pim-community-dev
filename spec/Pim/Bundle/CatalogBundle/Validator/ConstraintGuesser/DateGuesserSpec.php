<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class DateGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\DateGuesser');
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_supports_date_attributes(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);
    }

    function it_does_not_support_other_attributes(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
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

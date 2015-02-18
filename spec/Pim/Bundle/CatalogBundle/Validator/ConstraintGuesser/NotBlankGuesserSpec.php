<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class NotBlankGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_supports_any_attributes(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_image');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);
    }

    function it_guesses_required(AttributeInterface $attribute)
    {
        $attribute->isRequired()
            ->willReturn(true)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
    }

    function it_does_not_guess_non_required(AttributeInterface $attribute)
    {
        $attribute->isRequired()
            ->willReturn(false)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class EmailGuesserSpec extends ObjectBehavior
{
    public function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    public function it_supports_text_attributes(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);
    }

    public function it_does_not_support_other_attributes(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_image');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    public function it_guesses_email(AttributeInterface $attribute)
    {
        $attribute->getValidationRule()
            ->willReturn('email')
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Email');
    }

    public function it_does_not_guess_email(AttributeInterface $attribute)
    {
        $attribute->getValidationRule()
            ->willReturn('not_email')
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

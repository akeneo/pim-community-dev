<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;

class ChainedAttributeConstraintGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\ChainedAttributeConstraintGuesser');
    }

    function it_supports_attribute(AttributeInterface $attribute)
    {
        $this->supportAttribute($attribute)
            ->shouldReturn(true);
    }

    function it_can_add_a_constraint_guesser(ConstraintGuesserInterface $constraintGuesser)
    {
        $this->addConstraintGuesser($constraintGuesser)
            ->shouldReturn(null);
        $this->getConstraintGuessers()
            ->shouldReturn([
                $constraintGuesser
            ]);
    }

    function it_can_guess_constraints(
        AttributeInterface $attribute,
        ConstraintGuesserInterface $guesserFoo,
        ConstraintGuesserInterface $guesserBar,
        ConstraintGuesserInterface $guesserBaz
    ) {
        $guesserFoo->guessConstraints($attribute)
            ->willReturn(['foo']);
        $guesserBar->guessConstraints($attribute)
            ->willReturn(['bar']);
        $guesserBaz->guessConstraints($attribute)
            ->willReturn(['baz']);

        $guesserFoo->supportAttribute($attribute)
            ->willReturn(true);
        $guesserBar->supportAttribute($attribute)
            ->willReturn(false);
        $guesserBaz->supportAttribute($attribute)
            ->willReturn(true);

        $this->addConstraintGuesser($guesserFoo);
        $this->addConstraintGuesser($guesserBar);
        $this->addConstraintGuesser($guesserBaz);
        $this->guessConstraints($attribute)
            ->shouldReturn([
                'foo',
                'baz'
            ]);
    }
}

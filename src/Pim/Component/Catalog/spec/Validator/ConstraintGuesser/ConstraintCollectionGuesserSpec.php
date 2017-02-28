<?php

namespace spec\Pim\Component\Catalog\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesser\ConstraintCollectionGuesser;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraints\All;

class ConstraintCollectionGuesserSpec extends ObjectBehavior
{
    function let(AttributeInterface $textCollection, AttributeInterface $identifier)
    {
        $textCollection->getAttributeType()->willReturn('pim_catalog_text_collection');
        $identifier->getAttributeType()->willReturn('pim_catalog_identifier');
   }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConstraintCollectionGuesser::class);
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_supports_text_collection_attributes($textCollection)
    {
        $this->supportAttribute($textCollection)->shouldReturn(true);
    }

    function it_does_not_support_other_attributes($identifier, AttributeInterface $image)
    {
        $this->supportAttribute($identifier)->shouldReturn(false);

        $image->getAttributeType()->willReturn('pim_catalog_image');
        $this->supportAttribute($image)->shouldReturn(false);
    }

    function it_guesses_url($textCollection)
    {
        $textCollection->getValidationRule()->willReturn('url');
        $constraints = $this->guessConstraints($textCollection);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf(All::class);
    }

    function it_guesses_email($textCollection)
    {
        $textCollection->getValidationRule()->willReturn('email');
        $constraints = $this->guessConstraints($textCollection);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf(All::class);
    }

    function it_guesses_regex($textCollection)
    {
        $textCollection->getValidationRule()->willReturn('regexp');
        $textCollection->getValidationRegexp()->willReturn('a_pattern');
        $constraints = $this->guessConstraints($textCollection);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf(All::class);
    }

    function it_does_not_guess_other_constraints($textCollection)
    {
        $textCollection->getValidationRule()->willReturn('foobar');
        $constraints = $this->guessConstraints($textCollection);

        $constraints->shouldReturn([]);
    }
}

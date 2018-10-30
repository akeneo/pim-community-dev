<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UrlGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class UrlGuesserSpec extends ObjectBehavior
{
    function let(AttributeInterface $text, AttributeInterface $identifier, AttributeInterface $textarea)
    {
        $text->getType()->willReturn('pim_catalog_text');
        $identifier->getType()->willReturn('pim_catalog_identifier');
        $textarea->getType()->willReturn('pim_catalog_textarea');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UrlGuesser::class);
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_supports_text_attributes($text)
    {
        $this->supportAttribute($text)->shouldReturn(true);
    }

    function it_does_not_support_other_attributes($identifier, $textarea, AttributeInterface $image)
    {
        $this->supportAttribute($identifier)->shouldReturn(false);
        $this->supportAttribute($textarea)->shouldReturn(false);

        $image->getType()->willReturn('pim_catalog_image');
        $this->supportAttribute($image)->shouldReturn(false);
    }

    function it_guesses_url($text)
    {
        $text->getValidationRule()->willReturn('url');
        $constraints = $this->guessConstraints($text);

        $constraints->shouldHaveCount(1);

        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Url');
    }

    function it_does_not_guess_url($text)
    {
        $text->getValidationRule()->willReturn('not_url');
        $constraints = $this->guessConstraints($text);

        $constraints->shouldReturn([]);
    }
}

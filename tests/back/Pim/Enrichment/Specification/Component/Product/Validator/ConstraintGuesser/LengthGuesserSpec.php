<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\LengthGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class LengthGuesserSpec extends ObjectBehavior
{
    function let(AttributeInterface $text, AttributeInterface $identifier, AttributeInterface $textarea)
    {
        $text->getType()->willReturn('pim_catalog_text');
        $identifier->getType()->willReturn('pim_catalog_identifier');
        $textarea->getType()->willReturn('pim_catalog_textarea');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LengthGuesser::class);
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_supports_text_attributes($text, $identifier, $textarea, AttributeInterface $image)
    {
        $image->getType()->willReturn('pim_catalog_image');

        $this->supportAttribute($text)->shouldReturn(true);
        $this->supportAttribute($identifier)->shouldReturn(true);
        $this->supportAttribute($textarea)->shouldReturn(true);

        $this->supportAttribute($image)->shouldReturn(false);
    }

    function it_enforces_database_length_constraints($text, $textarea)
    {
        $text->getMaxCharacters()->willReturn(null);
        $textarea->getMaxCharacters()->willReturn(null);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraints[0]->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textConstraints[0]->max->shouldBe(255);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraints[0]->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textareaConstraints[0]->max->shouldBe(65535);
    }

    function it_enforces_the_max_characters_constraint($text, $textarea)
    {
        $text->getMaxCharacters()->willReturn(100);
        $textarea->getMaxCharacters()->willReturn(500);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraint = $textConstraints[0];
        $textConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textConstraint->max->shouldBe(100);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraint = $textareaConstraints[0];
        $textareaConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textareaConstraint->max->shouldBe(500);
    }
}

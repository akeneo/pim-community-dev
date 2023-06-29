<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\LengthGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class LengthGuesserSpec extends ObjectBehavior
{
    function let(AttributeInterface $text, AttributeInterface $identifier, AttributeInterface $textarea)
    {
        $text->getType()->willReturn('pim_catalog_text');
        $text->getCode()->willReturn('a_text');
        $identifier->getType()->willReturn('pim_catalog_identifier');
        $identifier->getCode()->willReturn('sku');
        $textarea->getType()->willReturn('pim_catalog_textarea');
        $textarea->getCode()->willReturn('a_textarea');
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

    function it_applies_the_same_constraint_for_an_identifier_attribute_as_it_does_the_others($identifier)
    {
        $identifier->getMaxCharacters()->willReturn(null);
        $lengthConstraints = $this->guessConstraints($identifier);

        $lengthConstraints->shouldHaveCount(1);
        $lengthConstraints[0]->shouldBeAnInstanceOf(Length::class);
        $lengthConstraints[0]->max->shouldBe(255);
        $lengthConstraints[0]->attributeCode->shouldBe('sku');
    }

    function it_enforces_database_length_constraints($text, $textarea)
    {
        $text->getMaxCharacters()->willReturn(null);
        $textarea->getMaxCharacters()->willReturn(null);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraints[0]->shouldBeAnInstanceOf(Length::class);
        $textConstraints[0]->max->shouldBe(255);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraints[0]->shouldBeAnInstanceOf(Length::class);
        $textareaConstraints[0]->max->shouldBe(65535);
    }

    function it_enforces_the_max_characters_constraint($text, $textarea)
    {
        $text->getMaxCharacters()->willReturn(100);
        $textarea->getMaxCharacters()->willReturn(500);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraint = $textConstraints[0];
        $textConstraint->shouldBeAnInstanceOf(Length::class);
        $textConstraint->max->shouldBe(100);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraint = $textareaConstraints[0];
        $textareaConstraint->shouldBeAnInstanceOf(Length::class);
        $textareaConstraint->max->shouldBe(500);
    }
}

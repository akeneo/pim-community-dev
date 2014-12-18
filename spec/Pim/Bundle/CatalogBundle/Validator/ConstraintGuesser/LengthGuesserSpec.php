<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class LengthGuesserSpec extends ObjectBehavior
{
    function let(AttributeInterface $text, AttributeInterface $identifier, AttributeInterface $textarea)
    {
        $text->getAttributeType()->willReturn('pim_catalog_text');
        $identifier->getAttributeType()->willReturn('pim_catalog_identifier');
        $textarea->getAttributeType()->willReturn('pim_catalog_textarea');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\LengthGuesser');
    }

    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_supports_text_attributes($text, $identifier, $textarea, AttributeInterface $image)
    {
        $image->getAttributeType()->willReturn('pim_catalog_image');

        $this->supportAttribute($text)->shouldReturn(true);
        $this->supportAttribute($identifier)->shouldReturn(true);
        $this->supportAttribute($textarea)->shouldReturn(true);

        $this->supportAttribute($image)->shouldReturn(false);
    }

    function it_enforces_database_length_constraints($text, $identifier, $textarea)
    {
        $text->getMaxCharacters()->willReturn(null);
        $identifier->getMaxCharacters()->willReturn(null);
        $textarea->getMaxCharacters()->willReturn(null);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraints[0]->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textConstraints[0]->max->shouldBe(255);

        $identifierConstraints = $this->guessConstraints($identifier);

        $identifierConstraints->shouldHaveCount(1);
        $identifierConstraints[0]->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $identifierConstraints[0]->max->shouldBe(255);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraints[0]->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textareaConstraints[0]->max->shouldBe(65535);
    }

    function it_enforces_the_max_characters_constraint($text, $identifier, $textarea)
    {
        $text->getMaxCharacters()->willReturn(100);
        $identifier->getMaxCharacters()->willReturn(200);
        $textarea->getMaxCharacters()->willReturn(500);

        $textConstraints = $this->guessConstraints($text);

        $textConstraints->shouldHaveCount(1);
        $textConstraint = $textConstraints[0];
        $textConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textConstraint->max->shouldBe(100);

        $identifierConstraints = $this->guessConstraints($identifier);

        $identifierConstraints->shouldHaveCount(1);
        $identifierConstraint = $identifierConstraints[0];
        $identifierConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $identifierConstraint->max->shouldBe(200);

        $textareaConstraints = $this->guessConstraints($textarea);

        $textareaConstraints->shouldHaveCount(1);
        $textareaConstraint = $textareaConstraints[0];
        $textareaConstraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Length');
        $textareaConstraint->max->shouldBe(500);
    }
}

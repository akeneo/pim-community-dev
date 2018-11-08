<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class RegexGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_identifier');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_guesses_regex(AttributeInterface $attribute)
    {
        $attribute->getValidationRule()
            ->willReturn('regexp')
            ->shouldBeCalled();
        $attribute->getValidationRegexp()
            ->willReturn('/.*/')
            ->shouldBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraints\Regex');
        $constraint->pattern
            ->shouldBe('/.*/');
    }

    function it_does_not_guess_non_regex_rule(AttributeInterface $attribute)
    {
        $attribute->getValidationRule()
            ->willReturn('not_regexp')
            ->shouldBeCalled();
        $attribute->getValidationRegexp()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);

        $attribute->getValidationRule()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getValidationRegexp()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_empty_regex(AttributeInterface $attribute)
    {
        $attribute->getValidationRule()
            ->willReturn('regexp')
            ->shouldBeCalled();
        $attribute->getValidationRegexp()
            ->willReturn('')
            ->shouldBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);

        $attribute->getValidationRegexp()
            ->willReturn(null)
            ->shouldBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class RangeGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getType()
            ->willReturn('pim_catalog_metric');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_number');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_date');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);

        $attribute->getType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_guesses_non_date_min(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');
        $attribute->getNumberMin()->willReturn(5);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isNegativeAllowed()->willReturn(null);

        $attribute->getDateMin()->shouldNotBeCalled();
        $attribute->getDateMax()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(doubleval(5));
        $constraint->max->shouldBe(null);
    }

    function it_guesses_non_date_max(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');
        $attribute->getNumberMin()->willReturn(null);
        $attribute->getNumberMax()->willReturn(10);
        $attribute->isNegativeAllowed()->willReturn(null);

        $attribute->getDateMin()->shouldNotBeCalled();
        $attribute->getDateMax()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(null);
        $constraint->max->shouldBe(doubleval(10));
    }

    function it_guesses_non_date_min_and_max(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');
        $attribute->getNumberMin()->willReturn(5);
        $attribute->getNumberMax()->willReturn(10);
        $attribute->isNegativeAllowed()->willReturn(null);

        $attribute->getDateMin()->shouldNotBeCalled();
        $attribute->getDateMax()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(doubleval(5));
        $constraint->max->shouldBe(doubleval(10));
    }

    function it_guesses_non_date_min_negative_allowed(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');
        $attribute->getNumberMin()->willReturn(-5);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isNegativeAllowed()->willReturn(true);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(doubleval(-5));
        $constraint->max->shouldBe(null);
    }

    function it_guesses_non_date_min_negative_not_allowed_will_be_zero(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');
        $attribute->getNumberMin()->willReturn(-5);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isNegativeAllowed()->willReturn(false);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(doubleval(0));
        $constraint->max->shouldBe(null);
    }

    function it_guesses_date_min(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getDateMin()->willReturn('1970-01-01');
        $attribute->getDateMax()->willReturn(null);

        $attribute->getNumberMin()->shouldNotBeCalled();
        $attribute->getNumberMax()->shouldNotBeCalled();
        $attribute->isNegativeAllowed()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe('1970-01-01');
        $constraint->max->shouldBe(null);
    }

    function it_guesses_date_max(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn('2038-01-19');

        $attribute->getNumberMin()->shouldNotBeCalled();
        $attribute->getNumberMax()->shouldNotBeCalled();
        $attribute->isNegativeAllowed()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe(null);
        $constraint->max->shouldBe('2038-01-19');
    }

    function it_guesses_date_min_and_max(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getDateMin()->willReturn('1970-01-01');
        $attribute->getDateMax()->willReturn('2038-01-19');

        $attribute->getNumberMin()->shouldNotBeCalled();
        $attribute->getNumberMax()->shouldNotBeCalled();
        $attribute->isNegativeAllowed()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(Range::class);
        $constraint->min->shouldBe('1970-01-01');
        $constraint->max->shouldBe('2038-01-19');
    }

    function it_does_not_guess_min_max(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');

        $attribute->getDateMin()->shouldNotBeCalled();
        $attribute->getDateMax()->shouldNotBeCalled();

        $attribute->getNumberMin()->willReturn(null);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isNegativeAllowed()->willReturn(null);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_min_max_numeric(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_date');

        $attribute->getDateMin()->shouldNotBeCalled();
        $attribute->getDateMax()->shouldNotBeCalled();

        $attribute->getNumberMin()->willReturn(null);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isNegativeAllowed()->willReturn(null);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_minmax_date(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_date');

        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);

        $attribute->getNumberMin()->shouldNotBeCalled();
        $attribute->getNumberMax()->shouldNotBeCalled();
        $attribute->isNegativeAllowed()->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

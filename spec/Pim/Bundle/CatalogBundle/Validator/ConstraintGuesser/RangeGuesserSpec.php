<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class RangeGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
    }

    function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_metric');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_number');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date');
        $this->supportAttribute($attribute)
            ->shouldReturn(true);

        $attribute->getAttributeType()
            ->willReturn('pim_catalog_text');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);

        $attribute->getAttributeType()
            ->willReturn('foo');
        $this->supportAttribute($attribute)
            ->shouldReturn(false);
    }

    function it_guesses_non_date_min(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();
        $attribute->getNumberMin()
            ->willReturn(5)
            ->shouldBeCalled();
        $attribute->getNumberMax()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->isNegativeAllowed()
            ->willReturn(null)
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldNotBeCalled();
        $attribute->getDateMax()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(5);
        $constraint->max
            ->shouldBe(null);
    }

    function it_guesses_non_date_max(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();
        $attribute->getNumberMin()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getNumberMax()
            ->willReturn(10)
            ->shouldBeCalled();
        $attribute->isNegativeAllowed()
            ->willReturn(null)
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldNotBeCalled();
        $attribute->getDateMax()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(null);
        $constraint->max
            ->shouldBe(10);
    }

    function it_guesses_non_date_min_and_max(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();
        $attribute->getNumberMin()
            ->willReturn(5)
            ->shouldBeCalled();
        $attribute->getNumberMax()
            ->willReturn(10)
            ->shouldBeCalled();
        $attribute->isNegativeAllowed()
            ->willReturn(null)
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldNotBeCalled();
        $attribute->getDateMax()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(5);
        $constraint->max
            ->shouldBe(10);
    }

    function it_guesses_non_date_min_negative_allowed(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();
        $attribute->getNumberMin()
            ->willReturn(-5)
            ->shouldBeCalled();
        $attribute->getNumberMax()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->isNegativeAllowed()
            ->willReturn(true)
            ->shouldBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(-5);
        $constraint->max
            ->shouldBe(null);
    }

    function it_guesses_non_date_min_negative_not_allowed_will_be_zero(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();
        $attribute->getNumberMin()
            ->willReturn(-5)
            ->shouldBeCalled();
        $attribute->getNumberMax()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->isNegativeAllowed()
            ->willReturn(false)
            ->shouldBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(0);
        $constraint->max
            ->shouldBe(null);
    }

    function it_guesses_date_min(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date')
            ->shouldBeCalled();
        $attribute->getDateMin()
            ->willReturn('1970-01-01')
            ->shouldBeCalled();
        $attribute->getDateMax()
            ->willReturn(null)
            ->shouldBeCalled();

        $attribute->getNumberMin()
            ->shouldNotBeCalled();
        $attribute->getNumberMax()
            ->shouldNotBeCalled();
        $attribute->isNegativeAllowed()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe('1970-01-01');
        $constraint->max
            ->shouldBe(null);
    }

    function it_guesses_date_max(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date')
            ->shouldBeCalled();
        $attribute->getDateMin()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getDateMax()
            ->willReturn('2038-01-19')
            ->shouldBeCalled();

        $attribute->getNumberMin()
            ->shouldNotBeCalled();
        $attribute->getNumberMax()
            ->shouldNotBeCalled();
        $attribute->isNegativeAllowed()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe(null);
        $constraint->max
            ->shouldBe('2038-01-19');
    }

    function it_guesses_date_min_and_max(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date')
            ->shouldBeCalled();
        $attribute->getDateMin()
            ->willReturn('1970-01-01')
            ->shouldBeCalled();
        $attribute->getDateMax()
            ->willReturn('2038-01-19')
            ->shouldBeCalled();

        $attribute->getNumberMin()
            ->shouldNotBeCalled();
        $attribute->getNumberMax()
            ->shouldNotBeCalled();
        $attribute->isNegativeAllowed()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
        $constraint->min
            ->shouldBe('1970-01-01');
        $constraint->max
            ->shouldBe('2038-01-19');
    }

    function it_does_not_guess_minmax(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldNotBeCalled();
        $attribute->getDateMax()
            ->shouldNotBeCalled();

        $attribute->getNumberMin()
            ->shouldBeCalled()
            ->willReturn(null);
        $attribute->getNumberMax()
            ->shouldBeCalled()
            ->willReturn(null);
        $attribute->isNegativeAllowed()
            ->shouldBeCalled()
            ->willReturn(null);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_minmax_numeric(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('not_date')
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldNotBeCalled();
        $attribute->getDateMax()
            ->shouldNotBeCalled();

        $attribute->getNumberMin()
            ->shouldBeCalled()
            ->willReturn(null);
        $attribute->getNumberMax()
            ->shouldBeCalled()
            ->willReturn(null);
        $attribute->isNegativeAllowed()
            ->shouldBeCalled()
            ->willReturn(null);

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_minmax_date(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()
            ->willReturn('pim_catalog_date')
            ->shouldBeCalled();

        $attribute->getDateMin()
            ->shouldBeCalled()
            ->willReturn(null);
        $attribute->getDateMax()
            ->shouldBeCalled()
            ->willReturn(null);

        $attribute->getNumberMin()
            ->shouldNotBeCalled();
        $attribute->getNumberMax()
            ->shouldNotBeCalled();
        $attribute->isNegativeAllowed()
            ->shouldNotBeCalled();

        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }
}

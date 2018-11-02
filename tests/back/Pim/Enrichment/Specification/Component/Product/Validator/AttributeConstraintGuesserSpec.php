<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeConstraintGuesser;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class AttributeConstraintGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeConstraintGuesser::class);
    }

    function it_returns_not_blank_constraint(AttributeInterface $attribute)
    {
        $attribute->getBackendType()->willReturn(null);
        $attribute->isRequired()->willReturn(true);

        $constraint = $this->guessConstraints($attribute);
        $constraint->shouldHaveCount(1);
        $constraint->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
    }

    function it_returns_date_constraint(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(false);
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_DATE);

        $constraint = $this->guessConstraints($attribute);
        $constraint->shouldHaveCount(1);
        $constraint->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Date');
    }

    function it_returns_not_blank_and_date_constraints(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(true);
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_DATE);

        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldHaveCount(2);
        $constraints->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
        $constraints->offsetGet(1)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Date');
    }

    function it_returns_datetime_constraint(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(false);
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_DATETIME);

        $constraint = $this->guessConstraints($attribute);
        $constraint->shouldHaveCount(1);
        $constraint->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\DateTime');
    }

    function it_returns_not_blank_and_datetime_constraints(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(true);
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_DATETIME);

        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldHaveCount(2);
        $constraints->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
        $constraints->offsetGet(1)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\DateTime');
    }
}

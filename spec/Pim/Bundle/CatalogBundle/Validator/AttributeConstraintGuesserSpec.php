<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class AttributeConstraintGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser');
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
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATE);

        $constraint = $this->guessConstraints($attribute);
        $constraint->shouldHaveCount(1);
        $constraint->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Date');
    }

    function it_returns_not_blank_and_date_constraints(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(true);
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATE);

        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldHaveCount(2);
        $constraints->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
        $constraints->offsetGet(1)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Date');
    }

    function it_returns_datetime_constraint(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(false);
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATETIME);

        $constraint = $this->guessConstraints($attribute);
        $constraint->shouldHaveCount(1);
        $constraint->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\DateTime');
    }

    function it_returns_not_blank_and_datetime_constraints(AttributeInterface $attribute)
    {
        $attribute->isRequired()->willReturn(true);
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATETIME);

        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldHaveCount(2);
        $constraints->offsetGet(0)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\NotBlank');
        $constraints->offsetGet(1)->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\DateTime');
    }
}

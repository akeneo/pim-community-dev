<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Validator\Constraints\NotBlankProperties;
use Pim\Component\Catalog\AttributeTypes;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotBlankPropertiesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\NotBlankPropertiesValidator');
    }

    function it_validates_not_reference_data_attribute(
        $context,
        NotBlankProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => null]);
        $value
            ->getAttributeType()
            ->willReturn(AttributeTypes::NUMBER);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_validates_not_blank_property_value(
        $context,
        NotBlankProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => 'not_blank_value']);
        $value
            ->getAttributeType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_validate_blank_property_value(
        $context,
        NotBlankProperties $constraint,
        ConstraintViolationBuilderInterface $violation,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => null]);
        $value
            ->getAttributeType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }
}

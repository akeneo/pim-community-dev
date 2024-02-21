<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullPropertiesValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullProperties;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotNullPropertiesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith(['pim_reference_data_multiselect', 'pim_reference_data_simpleselect']);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotNullPropertiesValidator::class);
    }

    function it_validates_not_blank_property_value(
        $context,
        NotNullProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => 'not_blank_value']);
        $value
            ->getType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_validate_blank_property_value(
        $context,
        NotNullProperties $constraint,
        ConstraintViolationBuilderInterface $violationBuilder,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => null]);
        $value
            ->getType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('my_property')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_validate_missing_property_value(
        $context,
        NotNullProperties $constraint,
        ConstraintViolationBuilderInterface $violationBuilder,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn([]);
        $value
            ->getType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('my_property')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_validates_not_supported_attributes(
        $context,
        NotNullProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getType()
            ->willReturn(AttributeTypes::METRIC);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }
}

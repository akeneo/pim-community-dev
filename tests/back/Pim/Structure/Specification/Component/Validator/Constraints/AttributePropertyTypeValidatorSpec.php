<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributePropertyType;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributePropertyTypeValidator;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributePropertyTypeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, ValidatorInterface $validator)
    {
        $context->getValidator()->willReturn($validator);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributePropertyTypeValidator::class);
    }

    function it_only_validates_property_type_constraints(AttributeInterface $attribute)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$attribute, new IsNull()]);
    }

    function it_only_validates_attributes()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new \stdClass(),
                new AttributePropertyType(['type' => 'bool', 'properties' => ['default_value']]),
            ]
        );
    }

    function it_does_nothing_if_the_property_is_null(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        AttributeInterface $attribute
    ) {
        $constraint = new AttributePropertyType(['type' => 'bool', 'properties' => ['default_value']]);

        $attribute->getProperty('default_value')->shouldBeCalled()->willReturn(null);
        $validator->validate(Argument::cetera())->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_add_a_violation_if_the_property_type_is_right(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        AttributeInterface $attribute
    ) {
        $constraint = new AttributePropertyType(['type' => 'bool', 'properties' => ['default_value']]);

        $attribute->getProperty('default_value')->shouldBeCalled()->willReturn(true);

        $validator->validate(true, Argument::type(Type::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList()
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violations_if_the_property_types_are_wrong(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationInterface $defaultValueViolation,
        ConstraintViolationInterface $optionSortingViolation
    ) {
        $constraint = new AttributePropertyType(
            ['type' => 'bool', 'properties' => ['default_value', 'auto_option_sorting']]
        );

        $attribute->getProperty('default_value')->shouldBeCalled()->willReturn('test');
        $attribute->getProperty('auto_option_sorting')->shouldBeCalled()->willReturn(3);

        $defaultValueViolation->getMessage()->willReturn('error message');
        $defaultValueViolation->getParameters()->willReturn([]);
        $validator->validate('test', Argument::type(Type::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([$defaultValueViolation->getWrappedObject()])
        );

        $optionSortingViolation->getMessage()->willReturn('other error message');
        $optionSortingViolation->getParameters()->willReturn([]);
        $validator->validate(3, Argument::type(Type::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([$optionSortingViolation->getWrappedObject()])
        );

        $context->buildViolation('error message', [])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('default_value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation('other error message', [])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('auto_option_sorting')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}

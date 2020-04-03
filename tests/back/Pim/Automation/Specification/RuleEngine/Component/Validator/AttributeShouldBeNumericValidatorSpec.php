<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\AttributeShouldBeNumericValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeShouldBeNumericValidatorSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AttributeShouldBeNumericValidator::class);
    }

    function it_throw_an_exception_when_the_constraint_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', ['weight', new IsNull()]);
    }

    function it_does_nothing_if_the_value_is_null(GetAttributes $getAttributes, ExecutionContextInterface $context)
    {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, new AttributeShouldBeNumeric());
    }

    function it_does_nothing_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('unknown_attribute')->shouldBeCalled()->willReturn(null);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('unknown_attribute', new AttributeShouldBeNumeric());
    }

    function it_does_not_build_a_violation_if_attribute_is_a_number(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('length')->willReturn(new Attribute(
            'length',
            AttributeTypes::NUMBER,
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('length', new AttributeShouldBeNumeric());
    }

    function it_does_not_build_a_violation_if_attribute_is_a_price_collection(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('price')->willReturn(
            new Attribute(
                'price',
                AttributeTypes::PRICE_COLLECTION,
                [],
                false,
                false,
                null,
                false,
                'prices',
                []
            )
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('price', new AttributeShouldBeNumeric());
    }

    function it_builds_a_violation_if_attribute_is_not_numeric(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new AttributeShouldBeNumeric();
        $getAttributes->forCode('description')->willReturn(
            new Attribute(
                'description',
                AttributeTypes::TEXTAREA,
                [],
                false,
                false,
                null,
                false,
                'string',
                []
            )
        );

        $context->buildViolation($constraint->message, ['%attribute_code%' => 'description'])
                ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('description', $constraint);
    }
}

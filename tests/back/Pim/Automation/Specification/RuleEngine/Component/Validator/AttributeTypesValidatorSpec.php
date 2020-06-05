<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\AttributeTypesValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeTypesValidatorSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ExecutionContextInterface $executionContext)
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($executionContext);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AttributeTypesValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['EUR', new IsNull()]);
    }

    function it_does_not_validate_a_null_value(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $executionContext
    ) {
        $propertyAccessor->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, new AttributeTypes(['types' => ['pim_catalog_text']]));
    }

    function it_does_not_validate_a_non_string_value(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $executionContext
    ) {
        $propertyAccessor->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new AttributeTypes(['types' => ['pim_catalog_text']]));
    }

    function it_does_not_build_a_violation_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode('unknown')->shouldBeCalled()->willReturn(null);
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('unknown', new AttributeTypes(['types' => ['pim_catalog_text']]));
    }

    function it_does_not_build_a_violation_if_the_attribute_type_is_valid(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext
    ) {
        $constraint = new AttributeTypes(['types' => ['pim_catalog_text', 'pim_catalog_textarea']]);

        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(new Attribute(
            'name', 'pim_catalog_text', [], true, false, null, null, null, 'string', []
        ));
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('name', $constraint);
    }

    function it_builds_a_violation_if_the_attribute_type_is_invalid(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new AttributeTypes(['types' => ['pim_catalog_number', 'pim_catalog_metric']]);
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute(
                'name', 'pim_catalog_text', [], true, false, null, null, null, 'string', []
            )
        );
        $executionContext->buildViolation(
            $constraint->message,
            [
                '{{ attribute_code }}' => 'name',
                '{{ invalid_type }}' => 'pim_catalog_text',
                '{{ expected_types }}' => 'pim_catalog_number | pim_catalog_metric',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('name', $constraint);
    }
}

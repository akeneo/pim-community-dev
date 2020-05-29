<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CurrencyKey;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\CurrencyKeyValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CurrencyKeyValidatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($getAttributes, $propertyAccessor);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(CurrencyKeyValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['EUR', new IsNull()]);
    }

    function it_does_not_validate_a_null_value(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency']));
    }

    function it_does_not_validate_if_attribute_property_is_not_a_string(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $executionContext
    ) {
        $testValue = new \stdClass();
        $propertyAccessor->getValue($testValue, 'field')->willReturn(null);
        $propertyAccessor->getValue($testValue, 'currency')->willReturn('EUR');

        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $testValue,
            new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency'])
        );
    }

    function it_does_not_add_a_violation_if_currency_is_set_and_attribute_is_a_price_collection(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $context
    ) {
        $testValue = new \stdClass();
        $propertyAccessor->getValue($testValue, 'field')->willReturn('price');
        $propertyAccessor->getValue($testValue, 'currency')->willReturn('EUR');
        $getAttributes->forCode('price')->willReturn(new Attribute(
            'price', 'pim_catalog_price_collection', [], false, false, null, null, null, 'prices', []
        ));

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $testValue,
            new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency'])
        );
    }

    function it_does_not_add_a_violation_if_currency_is_not_set_and_attribute_is_not_a_price_collection(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $context
    ) {
        $testValue = new \stdClass();
        $propertyAccessor->getValue($testValue, 'field')->willReturn('name');
        $propertyAccessor->getValue($testValue, 'currency')->willReturn(null);
        $getAttributes->forCode('name')->willReturn(
            new Attribute(
                'name', 'pim_catalog_text', [], false, false, null, null, null, 'string', []
            )
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $testValue,
            new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency'])
        );
    }

    function it_adds_a_violation_if_currency_is_not_set_and_attribute_is_a_price_collection(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency']);
        $testValue = new \stdClass();
        $propertyAccessor->getValue($testValue, 'field')->willReturn('price');
        $propertyAccessor->getValue($testValue, 'currency')->willReturn(null);
        $getAttributes->forCode('price')->willReturn(
            new Attribute(
                'price', 'pim_catalog_price_collection', [], false, false, null, null, null, 'prices', []
            )
        );

        $context->buildViolation(
            $constraint->emptyKeyMessage,
            [
                '{{ key }}' => 'currency',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('currency')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue(null)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($testValue, $constraint);
    }

    function it_adds_a_violation_if_currency_set_and_attribute_is_not_a_price_collection(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $testValue = new \stdClass();
        $constraint = new CurrencyKey(['attributeProperty' => 'field', 'currencyProperty' => 'currency']);
        $propertyAccessor->getValue($testValue, 'field')->willReturn('name');
        $propertyAccessor->getValue($testValue, 'currency')->willReturn('EUR');
        $getAttributes->forCode('name')->willReturn(
            new Attribute(
                'name', 'pim_catalog_text', [], false, false, null, null, null, 'string', []
            )
        );

        $context->buildViolation(
            $constraint->unexpectedKeyMessage,
            [
                '{{ key }}' => 'currency',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('currency')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('EUR')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($testValue, $constraint);
    }
}

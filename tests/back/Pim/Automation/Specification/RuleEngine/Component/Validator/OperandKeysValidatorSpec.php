<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\OperandKeys;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\OperandKeysValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class OperandKeysValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(OperandKeysValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new Operand(['value' => 10.0]), new IsNull()]
        );
    }

    function it_throws_an_exception_if_value_is_neither_an_operand_nor_an_operation()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new OperandKeys()]);
    }

    function it_does_not_build_a_violation_if_operand_only_has_a_value(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new Operand(['value' => 10.0]), new OperandKeys());
    }

    function it_does_not_build_a_violation_if_operand_only_has_a_field(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new Operand(['field' => 'foo']), new OperandKeys());
    }

    function it_does_not_build_a_violation_if_operand_has_a_field_with_other_options(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new Operation(
                [
                    'operator' => 'multiply',
                    'field' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'currency' => 'USD',
                ]
            ),
            new OperandKeys()
        );
    }

    function it_adds_a_violation_if_operand_has_neither_field_nor_value(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new OperandKeys();
        $context->buildViolation($constraint->requiredKeyMessage)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new Operand([]), $constraint);
    }

    function it_adds_a_violation_if_operand_has_both_field_and_value(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new OperandKeys();
        $context->buildViolation($constraint->onlyOneKeyExpectedKeyMessage)
                ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new Operand(['field' => 'length', 'value' => 3.14]), $constraint);
    }

    function it_adds_a_violation_if_operand_has_a_value_with_a_scope(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new OperandKeys();
        $context->buildViolation(
            $constraint->unexpectedKeyMessage,
            [
                '{{ key }}' => 'scope',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new Operand(['scope' => 'ecommerce', 'value' => 3.14]), $constraint);
    }

    function it_adds_a_violation_if_operand_has_a_value_with_a_locale(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new OperandKeys();
        $context->buildViolation(
            $constraint->unexpectedKeyMessage,
            [
                '{{ key }}' => 'locale',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new Operand(['locale' => 'en_US', 'value' => 3.14]), $constraint);
    }

    function it_adds_a_violation_if_operand_has_a_value_with_a_currency(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new OperandKeys();
        $context->buildViolation(
            $constraint->unexpectedKeyMessage,
            [
                '{{ key }}' => 'currency',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new Operand(['currency' => 'USD', 'value' => 3.14]), $constraint);
    }
}

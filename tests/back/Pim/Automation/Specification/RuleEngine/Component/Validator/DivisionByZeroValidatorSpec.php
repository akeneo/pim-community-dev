<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\DivisionByZero;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\DivisionByZeroValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DivisionByZeroValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DivisionByZeroValidator::class);
    }

    function it_throws_an_exception_with_an_invalid_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new Operation([]), new NotBlank()]);
    }

    function it_throws_an_exception_if_value_is_not_an_operation()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['test', new DivisionByZero()]);
    }

    function it_does_not_build_a_violation_if_operation_is_not_a_division(ExecutionContextInterface $context)
    {
        $operation = new Operation(['value' => 0.0, 'operator' => 'multiply']);
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($operation, new DivisionByZero());
    }

    function it_does_not_build_a_violation_if_value_is_null(ExecutionContextInterface $context)
    {
        $operation = new Operation(['field' => 'length', 'operator' => 'divide']);
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($operation, new DivisionByZero());
    }

    function it_does_not_build_a_violation_if_value_is_different_from_zero(ExecutionContextInterface $context)
    {
        $operation = new Operation(['value' => 0.5, 'operator' => 'divide']);
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($operation, new DivisionByZero());
    }

    function it_builds_a_violation_for_a_division_by_zero(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new DivisionByZero();
        $operation = new Operation(['operator' => 'divide', 'value' => 0.00]);
        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($operation, $constraint);
    }
}

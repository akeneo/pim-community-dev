<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\NonEmptyValueCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\NonEmptyValueConditionValidator;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NonEmptyValueConditionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NonEmptyValueConditionValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_adds_a_violation_if_the_operator_is_not_empty_and_the_value_is_empty(
        $context,
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $condition = new Condition([
            'operator' => 'foo',
        ]);

        $context->buildViolation(Argument::any(), Argument::any())->willReturn($violation);
        $violation->setInvalidValue(null)->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_empty_and_the_value_is_empty(
        ExecutionContextInterface $context,
        NonEmptyValueCondition $constraint
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $condition = new Condition([
            'operator' => Operators::IS_EMPTY,
        ]);

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_not_empty_and_the_value_is_empty(
        ExecutionContextInterface $context,
        NonEmptyValueCondition $constraint
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $condition = new Condition([
            'operator' => Operators::IS_NOT_EMPTY,
        ]);

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_unclassified_and_the_value_is_empty(
        ExecutionContextInterface $context,
        NonEmptyValueCondition $constraint
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $condition = new Condition(
            [
                'field' => 'categories',
                'operator' => Operators::UNCLASSIFIED,
            ]
        );

        $this->validate($condition, $constraint);
    }
}

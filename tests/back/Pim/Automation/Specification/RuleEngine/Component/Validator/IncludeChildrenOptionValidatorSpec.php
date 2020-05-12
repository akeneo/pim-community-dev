<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\RemoveAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IncludeChildrenOption;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IncludeChildrenOptionValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IncludeChildrenOptionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncludeChildrenOptionValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_throws_exception_if_it_is_not_an_include_children_constraint(Constraint $constraint)
    {
        $this->shouldThrow(
            new UnexpectedTypeException($constraint->getWrappedObject(), IncludeChildrenOption::class)
        )->during('validate', [Argument::any(), $constraint]);
    }

    function it_throws_exception_if_it_is_not_a_remove_action(
        IncludeChildrenOption $constraint
    ) {
        $this->shouldThrow(new \InvalidArgumentException(
            'Expected an instance of Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\RemoveAction. Got: stdClass'
        ))->during('validate', [new \stdClass(), $constraint]);
    }

    function it_skips_validation_if_include_children_is_not_set(
        ExecutionContextInterface $context,
        IncludeChildrenOption $constraint
    ) {
        $action = new RemoveAction([
            'field' => 'categories',
            'items' => ['dry'],
        ]);

        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($action, $constraint);
    }

    function it_adds_violation_if_it_is_not_the_right_field(
        ExecutionContextInterface $context,
        IncludeChildrenOption $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint->invalidFieldMessage = 'foo';

        $action = new RemoveAction([
            'field' => 'attribute_field',
            'items' => ['some_option'],
            'include_children' => true,
        ]);

        $context->buildViolation(
            'foo',
            [
                '%field%' => 'attribute_field'
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($action, $constraint);
    }
}

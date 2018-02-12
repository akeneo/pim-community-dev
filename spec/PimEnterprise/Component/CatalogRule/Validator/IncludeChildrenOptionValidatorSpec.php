<?php

namespace spec\PimEnterprise\Component\CatalogRule\Validator;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;
use PimEnterprise\Component\CatalogRule\Validator\Constraint\IncludeChildrenOption;
use PimEnterprise\Component\CatalogRule\Validator\IncludeChildrenOptionValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
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
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
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
        $this->shouldThrow(
            new \LogicException('Action of type "object" can not be validated.')
        )->during('validate', [new \stdClass(), $constraint]);
    }

    function it_skips_validation_if_include_children_is_not_set(
        $context,
        IncludeChildrenOption $constraint,
        ProductRemoveActionInterface $action
    ) {
        $action->getOptions()->willReturn([]);
        $action->getField()->shouldNotBeCalled();
        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($action, $constraint);
    }

    function it_adds_violation_if_it_is_not_the_right_field(
        $context,
        ProductRemoveActionInterface $action,
        IncludeChildrenOption $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint->invalidFieldMessage = 'foo';

        $action->getOptions()->willReturn([
            'include_children' => true,
        ]);
        $action->getField()->willReturn('attribute_field');

        $context->buildViolation(
            'foo',
            [
                '%field%' => 'attribute_field'
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($action, $constraint);
    }

    function it_adds_violation_if_include_children_is_not_a_boolean(
        $context,
        ProductRemoveActionInterface $action,
        IncludeChildrenOption $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint->invalidTypeMessage = 'foo';

        $action->getOptions()->willReturn(
            [
                'include_children' => 'bar',
            ]
        );
        $action->getField()->willReturn('categories');

        $context->buildViolation(
            'foo',
            [
                '%type%' => 'string'
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($action, $constraint);
    }
}

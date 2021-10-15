<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator;

use Akeneo\Pim\Permission\Component\Validator\UpdateUserGroupAttributeGroupsPermissions;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UpdateUserGroupAttributeGroupsPermissionsValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContextInterface $context,
        ValidatorInterface $validator
    ) {
        $this->initialize($context);
        $context->getValidator()->willReturn($validator);
    }

    public function it_does_nothing_if_valid(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violations
    ): void {
        $constraint = new UpdateUserGroupAttributeGroupsPermissions();
        $validator->validate(Argument::cetera())->willReturn($violations);
        $violations->count()->willReturn(0);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'edit' => [
                    'all' => false,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => false,
                    'identifiers' => [],
                ],
            ],
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_value_is_invalid(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violations
    ): void {
        $constraint = new UpdateUserGroupAttributeGroupsPermissions();
        $validator->validate(Argument::cetera())->willReturn($violations);
        $violations->count()->willReturn(1);
        $context->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate('invalid', $constraint);
    }
}

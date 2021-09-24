<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator;

use Akeneo\Pim\Permission\Component\Validator\UpdateUserGroupCategoriesPermissions;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UpdateUserGroupCategoriesPermissionsValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContextInterface $context
    ) {
        $this->initialize($context);
    }

    public function it_does_nothing_if_valid(
        ExecutionContextInterface $context
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => false,
                    'identifiers' => [],
                ],
                'edit' => [
                    'all' => false,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => false,
                    'identifiers' => [],
                ],
            ]
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_value_is_not_an_array(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate(false, $constraint);
    }

    public function it_adds_a_violation_if_the_user_group_is_not_a_string(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => false,
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_permissions_are_not_an_array(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => false,
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_permissions_does_not_declare_the_levels(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [],
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_option_all_is_not_a_boolean(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => null,
                    'identifiers' => [],
                ],
            ],
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_identifiers_are_not_an_array(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => false,
                    'identifiers' => null,
                ],
            ],
        ], $constraint);
    }

    public function it_adds_a_violation_if_the_all_is_selected_on_own_but_not_on_edit(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => true,
                    'identifiers' => [],
                ],
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

    public function it_adds_a_violation_if_the_all_is_selected_on_edit_but_not_on_view(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UpdateUserGroupCategoriesPermissions();
        $context->buildViolation($constraint->invalid_structure)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->validate([
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => false,
                    'identifiers' => [],
                ],
            ],
        ], $constraint);
    }
}

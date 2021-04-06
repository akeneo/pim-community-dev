<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Validator\Constraints\UniqueDatagridViewEntity;
use Oro\Bundle\PimDataGridBundle\Validator\Constraints\UniqueDatagridViewEntityValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueDatagridViewEntityValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        DatagridViewRepositoryInterface $datagridViewRepository
    ) {
        $this->beConstructedWith($datagridViewRepository);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueDatagridViewEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_public_datagrid_view_already_exists_with_the_same_label(
        ExecutionContextInterface $context,
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DatagridView $datagridView,
        DatagridView $datagridViewInDatabase
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $datagridView->getId()->willReturn(1);
        $datagridView->getType()->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->getLabel()->willReturn('The best public view');

        $datagridViewInDatabase->getId()->willReturn(2);

        $datagridViewRepository->findPublicDatagridViewByLabel('The best public view')
            ->willReturn($datagridViewInDatabase);

        $context->buildViolation('The same label is already set on another view')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($datagridView, $constraint);
    }

    function it_adds_violation_to_the_context_if_a_private_datagrid_view_already_exists_with_the_same_label_and_same_user(
        ExecutionContextInterface $context,
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DatagridView $datagridView,
        DatagridView $datagridViewInDatabase
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView->getId()->willReturn(1);
        $datagridView->getType()->willReturn(DatagridView::TYPE_PRIVATE);
        $datagridView->getLabel()->willReturn('The best private view');
        $datagridView->getOwner()->willReturn($user);

        $datagridViewInDatabase->getId()->willReturn(2);

        $datagridViewRepository->findPrivateDatagridViewByLabel('The best private view', $user)
            ->willReturn($datagridViewInDatabase);

        $context->buildViolation('The same label is already set on another view')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($datagridView, $constraint);
    }

    function it_does_not_add_violation_to_the_context_if_no_public_datagrid_view_already_exists_with_the_same_label(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DatagridView $datagridView
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $datagridView->getId()->willReturn(null);
        $datagridView->getType()->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->getLabel()->willReturn('The best public view');

        $datagridViewRepository->findPublicDatagridViewByLabel('The best public view')
            ->willReturn(null);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint);
    }

    function it_does_not_add_violation_to_the_context_if_no_private_datagrid_view_already_exists_with_the_same_label_and_same_user(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DatagridView $datagridView
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView->getId()->willReturn(null);
        $datagridView->getType()->willReturn(DatagridView::TYPE_PRIVATE);
        $datagridView->getLabel()->willReturn('The best private view');
        $datagridView->getOwner()->willReturn($user);

        $datagridViewRepository->findPrivateDatagridViewByLabel('The best private view', $user)
            ->willReturn(null);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint);
    }

    function it_does_not_add_violation_to_the_context_if_i_update_a_datagrid_view(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DatagridView $datagridView
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView->getId()->willReturn(null);
        $datagridView->getType()->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->getLabel()->willReturn('The best view');
        $datagridView->getOwner()->willReturn($user);

        $datagridViewRepository->findPublicDatagridViewByLabel('The best view')
            ->willReturn($datagridView);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint);
    }
}

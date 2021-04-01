<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
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
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $datagridView = new DatagridView();
        $datagridView->setId('1');
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setLabel('The best public view');

        $datagridViewInDatabase = clone $datagridView;
        $datagridViewInDatabase->setId('2');

        $datagridViewRepository->findPublicDatagridViewByLabel($datagridView->getLabel())
            ->willReturn($datagridViewInDatabase);

        $context->buildViolation('The same label is already set on another view')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($datagridView, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_to_the_context_if_a_private_datagrid_view_already_exists_with_the_same_label_and_same_user(
        ExecutionContextInterface $context,
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView = new DatagridView();
        $datagridView->setId('1');
        $datagridView->setType(DatagridView::TYPE_PRIVATE);
        $datagridView->setLabel('The best private view');
        $datagridView->setOwner($user);

        $datagridViewInDatabase = clone $datagridView;
        $datagridViewInDatabase->setId('2');

        $datagridViewRepository->findPrivateDatagridViewByLabel($datagridView->getLabel(), $datagridView->getOwner())
            ->willReturn($datagridViewInDatabase);

        $context->buildViolation('The same label is already set on another view')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($datagridView, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_to_the_context_if_no_public_datagrid_view_already_exists_with_the_same_label(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $datagridView = new DatagridView();
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setLabel('The best public view');

        $datagridViewRepository->findPublicDatagridViewByLabel($datagridView->getLabel())
            ->willReturn(null);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_to_the_context_if_no_private_datagrid_view_already_exists_with_the_same_label_and_same_user(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView = new DatagridView();
        $datagridView->setType(DatagridView::TYPE_PRIVATE);
        $datagridView->setLabel('The best private view');
        $datagridView->setOwner($user);

        $datagridViewRepository->findPrivateDatagridViewByLabel($datagridView->getLabel(), $datagridView->getOwner())
            ->willReturn(null);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_to_the_context_if_i_update_a_datagrid_view(
        DatagridViewRepositoryInterface $datagridViewRepository,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueDatagridViewEntity();

        $user = new User();

        $datagridView = new DatagridView();
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setLabel('The best view');
        $datagridView->setOwner($user);

        $datagridViewRepository->findPublicDatagridViewByLabel($datagridView->getLabel())
            ->willReturn($datagridView);

        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($datagridView, $constraint)->shouldReturn(null);
    }
}

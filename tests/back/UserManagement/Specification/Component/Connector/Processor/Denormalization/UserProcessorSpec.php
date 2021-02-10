<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\UserProcessor;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        DatagridViewRepositoryInterface $gridViewRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $factory, $updater, $validator, $objectDetacher, $gridViewRepository);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_user_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldHaveType(UserProcessor::class);
    }

    function it_raises_an_error_if_the_password_is_set(StepExecution $stepExecution)
    {
        $item = [
            'username' => 'admin',
            'password' => 'letmein',
        ];

        $stepExecution->getSummaryInfo('item_position')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_generates_a_temporary_password_for_a_new_user(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        UserInterface $user
    ) {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $repository->findOneByIdentifier('admin')->willReturn(null);
        $stepExecution->getExecutionContext()->willReturn(new ExecutionContext());

        $item = [
            'username' => 'admin',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $factory->create()->shouldBeCalled()->willReturn($user);
        $updater->update($user, Argument::that(
            function ($argument) use ($item): bool {
                $passwordIsSet = \is_array($argument) && isset($argument['password']) && '' !== $argument['password'];
                unset($argument['password']);

                return $passwordIsSet && $argument === $item;
            }
        ))->shouldBeCalled();
        $validator->validate($user)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process($item)->shouldReturn($user);
    }

    function it_sets_the_product_grid_view(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        DatagridViewRepositoryInterface $gridViewRepository,
        StepExecution $stepExecution,
        UserInterface $julia,
        DatagridView $productGridView
    ) {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $repository->findOneByIdentifier('julia')->willReturn($julia);
        $stepExecution->getExecutionContext()->willReturn(new ExecutionContext());
        $productGridView->getId()->willReturn(42);
        $gridViewRepository->findByLabelAndUser('My product grid view', $julia)->willReturn($productGridView);

        $updater->update($julia, [
            'username' => 'julia',
            'default_product_grid_view' => 42,
        ])->shouldBeCalled();
        $validator->validate($julia)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process([
            'username' => 'julia',
            'default_product_grid_view' => 'My product grid view',
        ])->shouldReturn($julia);
    }
}

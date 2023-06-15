<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\UserProcessor;
use Akeneo\UserManagement\Component\Model\UserInterface;
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
        FileStorerInterface $fileStorer,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
    ) {
        $this->beConstructedWith(
            $repository,
            $factory,
            $updater,
            $validator,
            $objectDetacher,
            $gridViewRepository,
            $fileStorer,
            $jobRepository,
            ['ignoredField1', 'ignoredField2'],
        );
        $stepExecution->getExecutionContext()->willReturn(new ExecutionContext());
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
        UserInterface $user
    ) {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $repository->findOneByIdentifier('admin')->willReturn(null);

        $item = [
            'username' => 'admin',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $user->getId()->willReturn(null);
        $factory->create()->shouldBeCalled()->willReturn($user);
        $updater->update($user, $item)->shouldBeCalled();
        $updater->update(
            $user,
            Argument::that(
                fn ($argument): bool => \is_array($argument) && 1 === \count($argument) && \is_string($argument['password'] ?? null)
            )
        )->shouldBeCalled();
        $validator->validate($user)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process($item)->shouldReturn($user);
    }

    function it_ignores_fields(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        JobRepositoryInterface $jobRepository,
        StepExecution $stepExecution,
        UserInterface $admin,
    )
    {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $admin->getId()->willReturn(44);
        $repository->findOneByIdentifier('admin')->willReturn($admin);

        $itemBase = [
            'username' => 'admin',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $item = [...$itemBase];
        $item['ignoredField1'] = 'value1';
        $item['ignoredField2'] = 'value2';

        $warning = new Warning(
            $stepExecution->getWrappedObject(),
            "The field(s) [ %ignoredFields% ] has been ignored",
            ['%ignoredFields%' => 'ignoredField1, ignoredField2'],
            $item
        );
        $jobRepository->addWarning($warning)->shouldBeCalled();

        $updater->update(
            $admin,
            $itemBase,
        )->shouldBeCalled();
        $validator->validate($admin)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process(
            $itemBase
        )->shouldReturn($admin);

        $this->process($item)->shouldReturn($admin);
    }

    function it_sets_the_product_grid_view(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        DatagridViewRepositoryInterface $gridViewRepository,
        UserInterface $julia,
        DatagridView $productGridView
    ) {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $julia->getId()->willReturn(44);
        $repository->findOneByIdentifier('julia')->willReturn($julia);
        $productGridView->getId()->willReturn(42);

        $gridViewRepository->findPrivateDatagridViewByLabel('My product grid view', $julia)->willReturn(null);
        $gridViewRepository->findPublicDatagridViewByLabel('My product grid view')->willReturn($productGridView);

        $updater->update(
            $julia,
            [
                'username' => 'julia',
                'default_product_grid_view' => 42,
            ]
        )->shouldBeCalled();
        $validator->validate($julia)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process(
            [
                'username' => 'julia',
                'default_product_grid_view' => 'My product grid view',
            ]
        )->shouldReturn($julia);
    }

    function it_stores_the_avatar_file(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        FileStorerInterface $fileStorer,
        UserInterface $julia,
        FileInfoInterface $fileInfo
    ) {
        $repository->getIdentifierProperties()->willReturn(['username']);
        $julia->getId()->willReturn(44);
        $repository->findOneByIdentifier('julia')->willReturn($julia);

        $fileStorer->store(Argument::type(\SplFileInfo::class), 'catalogStorage')->shouldBeCalled()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('a/b/c/123456789avatar.png');

        $updater->update($julia, [
            'username' => 'julia',
            'avatar' => [
                'filePath' => 'a/b/c/123456789avatar.png',
            ]
        ])->shouldBeCalled();
        $validator->validate($julia)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process(
            [
                'username' => 'julia',
                'avatar' => [
                    'filePath' => '/tmp/files/avatar.png',
                ],
            ]
        )->shouldReturn($julia);
    }
}

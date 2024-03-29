<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\RoleWithPermissionsProcessor;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Domain\Permissions\Query\EditRolePermissionsRoleQuery;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleWithPermissionsProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        ObjectUpdaterInterface $roleWithPermissionsUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
        $repository->getIdentifierProperties()->willReturn(['role']);
        $this->beConstructedWith($repository, $roleWithPermissionsFactory, $roleWithPermissionsUpdater, $validator, $editRolePermissionsRoleQuery);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissionsProcessor::class);
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_a_new_role(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        ObjectUpdaterInterface $roleWithPermissionsUpdater,
        ValidatorInterface $validator,
        ExecutionContext $executionContext,
        RoleInterface $role,
        EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
        $item = ['role' => 'ROLE_NEW', 'label' => 'the label', 'permissions' => ['action:privilege1']];
        $role->getId()->willReturn(null);
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $repository->findOneByIdentifier('ROLE_NEW')->shouldBeCalled()->willReturn(null);
        $editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions('ROLE_NEW')->willReturn(false);
        $executionContext->get('processed_items_batch')->shouldBeCalled()->willReturn([]);
        $roleWithPermissionsFactory->create()->shouldBeCalled()->willReturn($roleWithPermissions);
        $roleWithPermissionsUpdater->update($roleWithPermissions, $item)->shouldBeCalled();
        $validator->validate($roleWithPermissions)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $executionContext->put('processed_items_batch', ['ROLE_NEW' => $roleWithPermissions])->shouldBeCalled();

        $this->process($item)->shouldReturn($roleWithPermissions);
    }

    function it_processes_an_existing_role(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        ObjectUpdaterInterface $roleWithPermissionsUpdater,
        ValidatorInterface $validator,
        RoleInterface $role,
        EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
        $item = ['role' => 'ROLE_ADMIN', 'label' => 'the label', 'permissions' => ['action:privilege1']];
        $role->getId()->willReturn(42);
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $repository->findOneByIdentifier('ROLE_ADMIN')->shouldBeCalled()->willReturn($roleWithPermissions);
        $editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions('ROLE_ADMIN')->willReturn(false);
        $roleWithPermissionsFactory->create()->shouldNotBeCalled();
        $roleWithPermissionsUpdater->update($roleWithPermissions, $item)->shouldBeCalled();
        $validator->validate($roleWithPermissions)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->process($item)->shouldReturn($roleWithPermissions);
    }

    function it_throws_an_exception_when_validation_fails(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        ObjectUpdaterInterface $roleWithPermissionsUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        RoleInterface $role,
        EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
        $item = ['role' => 'ROLE_USER', 'label' => ''];

        $role->getId()->willReturn(42);
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $repository->findOneByIdentifier('ROLE_USER')->shouldBeCalled()->willReturn($roleWithPermissions);
        $editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions('ROLE_USER')->willReturn(false);
        $roleWithPermissionsFactory->create()->shouldNotBeCalled();
        $roleWithPermissionsUpdater->update($roleWithPermissions, $item)->shouldBeCalled();
        $validator->validate($roleWithPermissions)->willReturn(
            new ConstraintViolationList([
                new ConstraintViolation('message', null, [], null, null, null),
            ])
        );
        $stepExecution->getSummaryInfo('item_position')->willReturn(44);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    function it_adds_warning_when_removing_last_edit_role_permissions(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        StepExecution $stepExecution,
        RoleInterface $role,
        EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
        $item = ['role' => 'ROLE_ADMIN', 'label' => 'the label', 'permissions' => ['action:privilege1']];
        $role->getId()->willReturn(42);
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $repository->findOneByIdentifier('ROLE_ADMIN')->shouldBeCalled()->willReturn($roleWithPermissions);
        $editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions('ROLE_ADMIN')->willReturn(true);
        $roleWithPermissionsFactory->create()->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled()->willReturn(42);
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }
}

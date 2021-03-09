<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Connector\Writer\Database\RoleWithPermissionsWriter;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PhpSpec\ObjectBehavior;

class RoleWithPermissionsWriterSpec extends ObjectBehavior
{
    function let(BulkSaverInterface $roleWithPermissionsSaver, StepExecution $stepExecution)
    {
        $this->beConstructedWith($roleWithPermissionsSaver);
        $this->setStepExecution($stepExecution);
    }

    function it_is_instantiable()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissionsWriter::class);
    }

    function it_implements_several_interfaces()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_writes_roles_with_permissions(
        BulkSaverInterface $roleWithPermissionsSaver,
        StepExecution $stepExecution,
        RoleInterface $role1,
        RoleInterface $role2,
        RoleInterface $role3
    ) {
        $role1->getId()->willReturn(42);
        $roleWithPermissions1 = RoleWithPermissions::createFromRoleAndPermissions($role1->getWrappedObject(), []);
        $role2->getId()->willReturn(44);
        $roleWithPermissions2 = RoleWithPermissions::createFromRoleAndPermissions($role2->getWrappedObject(), []);
        $role3->getId()->willReturn(null);
        $roleWithPermissions3 = RoleWithPermissions::createFromRoleAndPermissions($role3->getWrappedObject(), []);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalledOnce();
        $roleWithPermissionsSaver->saveAll(
            [$roleWithPermissions1, $roleWithPermissions2, $roleWithPermissions3]
        )->shouldBeCalled();

        $this->write([$roleWithPermissions1, $roleWithPermissions2, $roleWithPermissions3]);
    }
}

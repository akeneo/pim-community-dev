<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Connector\Writer\Database\RoleWithPermissionsWriter;
use Akeneo\UserManagement\Component\Model\Role;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadata;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsWriterSpec extends ObjectBehavior
{
    function let(
        ItemWriterInterface $writer,
        AclManager $aclManager,
        StepExecution $stepExecution,
        AclExtensionInterface $extension
    ) {
        $aclManager->getAllExtensions()->willReturn([$extension]);
        $extension->getExtensionKey()->willReturn('action');
        $extension->getClasses()->willReturn([
            new ActionMetadata('list_product'),
            new ActionMetadata('create_product'),
            new ActionMetadata('delete_product'),
        ]);
        $extension->getAllMaskBuilders()->willReturn([]);

        $this->beConstructedWith($writer, $aclManager);
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
        $this->shouldImplement(FlushableInterface::class);
    }

    function it_writes_role_and_updates_permissions(ItemWriterInterface $writer, AclManager $aclManager)
    {
        $adminRole = new Role('ROLE_ADMIN');
        $userRole = new Role('ROLE_USER');
        $roleWithPermissions1 = RoleWithPermissions::createFromRoleAndPermissionIds(
            $adminRole,
            ['action:list_product', 'action:create_product']
        );
        $roleWithPermissions2 = RoleWithPermissions::createFromRoleAndPermissionIds(
            $userRole,
            ['action:list_product', 'action:delete_product']
        );

        $writer->write([$adminRole, $userRole])->shouldBeCalled();

        $createProductOid = new ObjectIdentity('action', 'create_product');
        $listProductOid = new ObjectIdentity('action', 'list_product');
        $deleteProductOid = new ObjectIdentity('action', 'delete_product');
        $aclManager->getRootOid('action')->WillReturn(new ObjectIdentity('id', 'type'));

        $adminSid = new RoleSecurityIdentity($adminRole->getRole());
        $aclManager->getSid($adminRole)->willReturn($adminSid);
        $aclManager->setPermission($adminSid, $listProductOid, 1, true)->shouldBeCalled();
        $aclManager->setPermission($adminSid, $createProductOid, 1, true)->shouldBeCalled();
        $aclManager->setPermission($adminSid, $deleteProductOid, 0, true)->shouldBeCalled();

        $userSid = new RoleSecurityIdentity($userRole->getRole());
        $aclManager->getSid($userRole)->willReturn($userSid);
        $aclManager->setPermission($userSid, $listProductOid, 1, true)->shouldBeCalled();
        $aclManager->setPermission($userSid, $createProductOid, 0, true)->shouldBeCalled();
        $aclManager->setPermission($userSid, $deleteProductOid, 1, true)->shouldBeCalled();

        $this->write([$roleWithPermissions1, $roleWithPermissions2]);
    }

    function it_flushes_the_permissions(AclManager $aclManager)
    {
        $aclManager->flush()->shouldBeCalled();

        $this->flush();
    }
}

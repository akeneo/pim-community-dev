<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Connector\Writer\Database\RoleWithPermissionsWriter;
use Akeneo\UserManagement\Component\Model\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsWriterSpec extends ObjectBehavior
{
    function let(
        ItemWriterInterface $writer,
        AclManager $aclManager,
        StepExecution $stepExecution
    ) {
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
    }

    function it_writes_role_and_updates_permissions(
        ItemWriterInterface $writer,
        AclManager $aclManager,
        AclPrivilegeRepository $privilegeRepository
    ) {
        $privilege1 = new AclPrivilege();
        $privilege2 = new AclPrivilege();

        $adminRole = new Role('ROLE_ADMIN');
        $userRole = new Role('ROLE_USER');
        $roleWithPermissions1 = RoleWithPermissions::createFromRoleAndPrivileges(
            $adminRole,
            [$privilege1, $privilege2]
        );
        $roleWithPermissions2 = RoleWithPermissions::createFromRoleAndPrivileges(
            $userRole,
            [$privilege2]
        );

        $writer->write([$adminRole, $userRole])->shouldBeCalled();

        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);
        $adminSid = new RoleSecurityIdentity('ROLE_ADMIN');
        $aclManager->getSid($adminRole)->willReturn($adminSid);
        $privilegeRepository->savePrivileges($adminSid, new ArrayCollection([$privilege1, $privilege2]))
                            ->shouldBeCalled();
        $userSid = new RoleSecurityIdentity('ROLE_USER');
        $aclManager->getSid($userRole)->willReturn($userSid);
        $privilegeRepository->savePrivileges($userSid, new ArrayCollection([$privilege2]))
                            ->shouldBeCalled();

        $this->write([$roleWithPermissions1, $roleWithPermissions2]);
    }
}

<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Updater\RoleWithPermissionsUpdater;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Webmozart\Assert\Assert;

class RoleWithPermissionsUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $roleUpdater, AclManager $aclManager)
    {
        $this->beConstructedWith($roleUpdater, $aclManager);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_is_a_role_with_permissions_updater()
    {
        $this->shouldHaveType(RoleWithPermissionsUpdater::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_but_a_role_with_permissions()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('update', [new \stdClass(), []]);
    }

    function it_updates_the_role(ObjectUpdaterInterface $roleUpdater, RoleInterface $role)
    {
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);
        $roleUpdater->update($role, ['role' => 'ROLE_USER'])->shouldBeCalled();
        $roleUpdater->update($role, ['label' => 'User'])->shouldBeCalled();

        $this->update($roleWithPermissions, ['role' => 'ROLE_USER', 'label' => 'User']);
    }

    function it_updates_the_permissions(
        AclManager $aclManager,
        AclPrivilegeRepository $privilegeRepository,
        AclPrivilege $rootPrivilege,
        AclPrivilege $privilege1,
        AclPrivilege $privilege2,
        RoleInterface $role
    ) {
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $sid = new RoleSecurityIdentity($role);
        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);
        $privilegeRepository->getPrivileges($sid)->willReturn(
            new ArrayCollection(
                [$rootPrivilege->getWrappedObject(), $privilege1->getWrappedObject(), $privilege2->getWrappedObject()]
            )
        );

        $rootPrivilege->getExtensionKey()->willReturn('action');
        $rootPrivilege->getIdentity()->willReturn(new AclPrivilegeIdentity('action:(default)', '(default)'));
        $privilege1->getExtensionKey()->willReturn('action');
        $privilege1->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege1', 'privilege1'));
        $privilege2->getExtensionKey()->willReturn('action');
        $privilege2->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege2', 'privilege2'));

        $aclManager->clearCache()->shouldBeCalled();

        $this->update($roleWithPermissions, ['permissions' => ['action:privilege1']]);
        Assert::same(
            $roleWithPermissions->permissions(),
            [
                'action:privilege1' => true,
                'action:privilege2' => false,
            ]
        );
    }

    function it_adds_non_existent_permissions(
        AclManager $aclManager,
        AclPrivilegeRepository $privilegeRepository,
        AclPrivilege $rootPrivilege,
        AclPrivilege $privilege1,
        RoleInterface $role
    ) {
        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), []);

        $sid = new RoleSecurityIdentity($role);
        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);
        $privilegeRepository->getPrivileges($sid)->willReturn(
            new ArrayCollection(
                [$rootPrivilege->getWrappedObject(), $privilege1->getWrappedObject()]
            )
        );

        $rootPrivilege->getExtensionKey()->willReturn('action');
        $rootPrivilege->getIdentity()->willReturn(new AclPrivilegeIdentity('action:(default)', '(default)'));
        $privilege1->getExtensionKey()->willReturn('action');
        $privilege1->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege1', 'privilege1'));

        $aclManager->clearCache()->shouldBeCalled();

        $this->update($roleWithPermissions, ['permissions' => ['action:non_existent_privilege']]);
        Assert::same(
            $roleWithPermissions->permissions(),
            [
                'action:privilege1' => false,
                'action:non_existent_privilege' => true,
            ]
        );
    }
}

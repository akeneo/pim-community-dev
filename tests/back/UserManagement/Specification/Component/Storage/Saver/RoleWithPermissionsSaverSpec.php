<?php

namespace Specification\Akeneo\UserManagement\Component\Storage\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        AclManager $aclManager
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher, $aclManager);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement(BulkSaverInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RoleWithPermissionsSaver::class);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_roles_with_permissions()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('saveAll', [[new \stdClass()]]);
    }

    function it_saves_roles_with_permissions(
        ObjectManager $objectManager,
        AclManager $aclManager,
        AclPrivilegeRepository $privilegeRepository,
        RoleInterface $role1,
        RoleInterface $role2,
        AclPrivilege $privilege1,
        AclPrivilege $privilege2,
        AclPrivilege $privilege3,
        AclPrivilege $privilege4,
        AclPermission $permission1,
        AclPermission $permission2,
        AclPermission $permission3,
        AclPermission $permission4
    ) {
        $role1->getId()->willReturn(42);
        $role1->getRole()->willReturn('ROLE_ADMIN');
        $role2->getId()->willReturn(null);
        $role2->getRole()->willReturn('ROLE_USER');

        $roleWithPermissions1 = RoleWithPermissions::createFromRoleAndPermissions($role1->getWrappedObject(), []);
        $roleWithPermissions2 = RoleWithPermissions::createFromRoleAndPermissions(
            $role2->getWrappedObject(),
            ['action:privilege1' => true, 'action:privilege2' => false]
        );

        $aclManager->getSid(Argument::type(RoleInterface::class))->shouldBeCalledTimes(2)->will(
            fn (...$role) => new RoleSecurityIdentity($role)
        );
        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);

        $privilege1->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege1'));
        $privilege1->getPermissions()->willReturn([$permission1]);
        $privilege2->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege2'));
        $privilege2->getPermissions()->willReturn([$permission2]);
        $privilegeCollection1 = new ArrayCollection([$privilege1->getWrappedObject(), $privilege2->getWrappedObject()]);

        $privilege3->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege1'));
        $privilege3->getPermissions()->willReturn([$permission3]);
        $privilege4->getIdentity()->willReturn(new AclPrivilegeIdentity('action:privilege2'));
        $privilege4->getPermissions()->willReturn([$permission4]);
        $privilegeCollection2 = new ArrayCollection([$privilege3->getWrappedObject(), $privilege4->getWrappedObject()]);

        $privilegeRepository->getPrivileges(
            Argument::type(RoleSecurityIdentity::class)
        )->shouldBeCalledTimes(2)->willReturn(
            $privilegeCollection1,
            $privilegeCollection2
        );

        $objectManager->persist($role1)->shouldBeCalled();
        $objectManager->persist($role2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $permission1->setAccessLevel(Argument::any())->shouldNotBeCalled();
        $permission2->setAccessLevel(Argument::any())->shouldNotBeCalled();
        $privilegeRepository->savePrivileges(Argument::type(RoleSecurityIdentity::class), $privilegeCollection1)
                            ->shouldBeCalled();

        $permission3->setAccessLevel(AccessLevel::SYSTEM_LEVEL)->shouldBeCalled();
        $permission4->setAccessLevel(AccessLevel::NONE_LEVEL)->shouldBeCalled();
        $privilegeRepository->savePrivileges(Argument::type(RoleSecurityIdentity::class), $privilegeCollection2)
                            ->shouldBeCalled();

        $this->saveAll([$roleWithPermissions1, $roleWithPermissions2]);
    }

    private function createPrivilege(string $id, bool $isGranted): AclPrivilege
    {
        $privilege = new AclPrivilege();
        $privilege->setExtensionKey('action');
        $privilege->setIdentity(new AclPrivilegeIdentity($id));
        $privilege->addPermission(
            new AclPermission('EXECUTE', $isGranted ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL)
        );

        return $privilege;
    }
}

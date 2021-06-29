<?php

namespace Specification\Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsRepositorySpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $roleRepository,
        AclManager $aclManager,
        AclPrivilegeRepository $privilegeRepository
    ) {
        $aclManager->getSid(Argument::type(RoleInterface::class))->will(
            fn (...$role): RoleSecurityIdentity => new RoleSecurityIdentity($role)
        );
        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);
        $this->beConstructedWith($roleRepository, $aclManager);
    }

    function it_is_an_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_role_with_permissions_repository()
    {
        $this->shouldHaveType(RoleWithPermissionsRepository::class);
    }

    function it_returns_the_identifier_properties(IdentifiableObjectRepositoryInterface $roleRepository)
    {
        $roleRepository->getIdentifierProperties()->shouldBeCalled()->willReturn(['role']);
        $this->getIdentifierProperties()->shouldReturn(['role']);
    }

    function it_returns_null_if_the_role_does_not_exist(IdentifiableObjectRepositoryInterface $roleRepository)
    {
        $roleRepository->findOneByIdentifier('ROLE_UNKNOWN')->shouldBeCalled()->willReturn(null);
        $this->findOneByIdentifier('ROLE_UNKNOWN')->shouldReturn(null);
    }

    function it_gets_a_role_with_its_granted_permissions(
        IdentifiableObjectRepositoryInterface $roleRepository,
        AclPrivilegeRepository $privilegeRepository
    ) {
        $role = new Role('ROLE_ADMIN');

        $roleRepository->findOneByIdentifier('ROLE_ADMIN')->shouldBeCalled()->willReturn($role);
        $privilegeRepository->getPrivileges(Argument::type(RoleSecurityIdentity::class))->willReturn(
            new ArrayCollection([
                $this->createAclprivilege('entity:(default)', true),
                $this->createAclPrivilege('action:(default)', true),
                $this->createAclprivilege('action:privilege1', true),
                $this->createAclprivilege('action:privilege2', false),
                $this->createAclprivilege('action:privilege3', true),
            ])
        );

        $this->findOneByIdentifier('ROLE_ADMIN')->shouldBeLike(RoleWithPermissions::createFromRoleAndPermissions(
            $role,
            [
                'action:privilege1' => true,
                'action:privilege2' => false,
                'action:privilege3' => true,
            ]
        ));
    }

    private function createAclprivilege(string $privilegeId, bool $isGranted): AclPrivilege
    {
        $privilege = new AclPrivilege();
        [$extensionKey, $privilegeName] = explode(':', $privilegeId);

        $privilege->setIdentity(new AclPrivilegeIdentity($privilegeId, $privilegeName));
        $privilege->setExtensionKey($extensionKey);
        $privilege->addPermission(
            new AclPermission(
                'EXECUTE',
                $isGranted ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL
            )
        );

        return $privilege;
    }
}

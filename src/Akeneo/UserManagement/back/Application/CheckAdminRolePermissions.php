<?php

namespace Akeneo\UserManagement\Application;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;

class CheckAdminRolePermissions
{
    public function __construct(
        private RoleRepository $roleRepository,
        private AclManager $aclManager,
    ) {
    }

    /**
     * @return array<UserInterface>
     */
    public function __invoke(): array
    {
        $roles = $this->roleRepository->findAll();
        /** @var RoleInterface[] $minimumPrivilegesRoles */
        $minimumPrivilegesRoles = [];
        $minimumAdminPrivileges = ['action:pim_user_role_edit','action:pim_user_role_index','action:pim_user_user_index','action:pim_user_user_edit', 'action:oro_config_system'];
        /** @var UserInterface[] $minimumPrivilegesRoles */
        $usersWithPrivileges = [];
        /** @var RoleInterface $role */
        foreach ($roles as $role) {
            /** @var ArrayCollection $privileges */
            $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));
            $minimumPrivileges = $privileges->filter(function (AclPrivilege $entry) use ($minimumAdminPrivileges) {
                $hasRights = $entry->getPermissions()->get('EXECUTE')->getAccessLevel() != AccessLevel::NONE_LEVEL;
                $isMinimumAdminPrivileges = in_array($entry->getIdentity()->getId(), $minimumAdminPrivileges);
                return $entry->getExtensionKey() === 'action' && $entry->isVisible() && $isMinimumAdminPrivileges && $hasRights;
            });
            if($minimumPrivileges->count() === count($minimumAdminPrivileges)) {
                $minimumPrivilegesRoles[] = $role;
            }
        }

        foreach ($minimumPrivilegesRoles as $role) {
            $userQueryBuilder = $this->roleRepository->getUserQueryBuilder($role);
            $users = $userQueryBuilder->getQuery()->execute();
            foreach ($users as $user) {
                if(!in_array($user, $usersWithPrivileges)) {
                    $usersWithPrivileges[] = $user;
                }
            }
        }

        return $usersWithPrivileges;
    }
}

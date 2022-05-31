<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Install;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddAclToRoles
{
    public function __construct(
        private AclManager $aclManager,
        private RoleRepository $roleRepository,
    ) {
    }

    /**
     * @param array<string> $roles
     */
    public function add(string $acl, array $roles): void
    {
        $aclPrivilegeIdentityId = \sprintf('action:%s', $acl);

        foreach ($roles as $role) {
            $role = $this->roleRepository->findOneByIdentifier($role);
            $privilege = new AclPrivilege();
            $identity = new AclPrivilegeIdentity($aclPrivilegeIdentityId);
            $privilege
                ->setIdentity($identity)
                ->addPermission(new AclPermission('EXECUTE', AccessLevel::BASIC_LEVEL));
            $this->aclManager->getPrivilegeRepository()->savePrivileges(
                $this->aclManager->getSid($role),
                new ArrayCollection([$privilege])
            );
        }
        $this->aclManager->flush();
        $this->aclManager->clearCache();
    }
}

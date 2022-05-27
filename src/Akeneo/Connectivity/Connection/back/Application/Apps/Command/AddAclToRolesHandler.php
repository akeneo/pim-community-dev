<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAclToRolesHandler
{
    public function __construct(
        private AclManager $aclManager,
        private RoleRepository $roleRepository,
    ) {
    }

    public function handle(AddAclToRolesCommand $command): void
    {
        $aclPrivilegeIdentityId = 'action:' . $command->getAcl();

        foreach ($command->getRoles() as $role) {
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

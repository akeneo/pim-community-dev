<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_4_1_20200127171059_unauthorized_view_all_jobs_permission_Integration extends TestCase
{
    private const ACL_ID = 'pim_enrich_job_tracker_view_all_jobs';
    private const PRIVILEGE_ID = 'action:pim_enrich_job_tracker_view_all_jobs';

    use ExecuteMigrationTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Connection */
    private $connection;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var RoleRepositoryInterface */
    private $roleRepository;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var AclManager */
    private $aclManager;

    /** @var ActionAclExtension */
    private $aclExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->get('security.token_storage');
        $this->connection = $this->get('database_connection');
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->securityFacade = $this->get('oro_security.security_facade');
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->aclExtension = $this->get('oro_security.acl.extension.action');
        $this->roleRepository = $this->get('pim_user.repository.role');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItForbidToViewAllJobsForAllRoles()
    {
        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $this->authorizedPermission($role);
        }

        $this->aclManager->flush();
        $this->aclManager->clearCache();

        foreach ($roles as $role) {
            $this->assertNotEquals(
                0,
                $this->findAccessLevelForRole($role),
                'A role is unauthorized to see all jobs before migration.'
            );
        }

        $this->reExecuteMigration($this->getMigrationLabel());
        $this->aclManager->clearCache();

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $this->assertNotEquals(
                1,
                $this->findAccessLevelForRole($role),
                'A role is authorized to see all jobs after migration.'
            );

        }
    }

    private function findAccessLevelForRole(Role $role): ?int
    {
        $aclPrivileges = $this->aclManager->getPrivilegeRepository()
            ->getPrivileges(new RoleSecurityIdentity($role->getRole()));
        foreach ($aclPrivileges as $aclPrivilege) {
            if ($aclPrivilege->getIdentity()->getId() === static::PRIVILEGE_ID) {
                return $aclPrivilege->getPermissions()->get('EXECUTE')->getAccessLevel();
            }
        }

        return null;
    }

    private function authorizedPermission(Role $role): void
    {
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity(static::PRIVILEGE_ID);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', 1));

        $this
            ->aclManager
            ->getPrivilegeRepository()
            ->savePrivileges(new RoleSecurityIdentity($role->getRole()), new ArrayCollection([$privilege]));
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}

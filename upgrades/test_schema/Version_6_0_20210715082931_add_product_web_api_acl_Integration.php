<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Role;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Version_6_0_20210715082931_add_product_web_api_acl_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const PRIVILEGE_ID = 'action:pim_api_product_edit';
    private AclManager $aclManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aclManager = $this->get('oro_security.acl.manager');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    public function test_if_product_web_api_acl_exists()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $this->assertEquals(AccessLevel::SYSTEM_LEVEL, $this->findAccessLevelForRole($adminRole));
    }

    private function findAccessLevelForRole(Role $role): ?int
    {
        $aclPrivileges = $this->aclManager
            ->getPrivilegeRepository()
            ->getPrivileges($this->aclManager->getSid($role));
        foreach ($aclPrivileges as $aclPrivilege) {
            if ($aclPrivilege->getIdentity()->getId() === self::PRIVILEGE_ID) {
                return $aclPrivilege->getPermissions()->get('EXECUTE')->getAccessLevel();
            }
        }

        return null;
    }
}

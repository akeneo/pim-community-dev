<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Akeneo\UserManagement\Component\Updater\RoleWithPermissionsUpdater;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Version_6_0_20210715082931_add_product_web_api_acl_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    /** @var string[] */
    private static array $acls = [
        'pim_api_product_list',
        'pim_api_product_edit',
    ];

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

    public function test_the_migration_adds_the_new_permissions_to_existing_roles()
    {
        /** @var SimpleFactoryInterface $roleFactory */
        $roleFactory = $this->get('pim_user.factory.role');
        /** @var SaverInterface $roleSaver */
        $roleSaver = $this->get('pim_user.saver.role');

        /** @var Role $role */
        $role = $roleFactory->create();
        $role->setRole('ROLE_WITHOUT_PERMS');
        $role->setLabel('Test');
        $roleSaver->save($role);

        foreach (self::$acls as $acl) {
            $this->assertIsNotGranted('ROLE_WITHOUT_PERMS', $acl);
        }

        $this->reExecuteMigration($this->getMigrationLabel());

        foreach (self::$acls as $acl) {
            $this->assertIsGranted('ROLE_WITHOUT_PERMS', $acl);
        }
    }

    private function assertIsGranted(string $role, string $acl): void
    {
        /** @var AccessDecisionManagerInterface $decisionManager */
        $decisionManager = $this->get('security.access.decision_manager');

        $token = new UsernamePasswordToken('test', null, 'main', [$role]);
        $isAllowed = $decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));

        $this->assertTrue($isAllowed);
    }

    private function assertIsNotGranted(string $role, string $acl): void
    {
        /** @var AccessDecisionManagerInterface $decisionManager */
        $decisionManager = $this->get('security.access.decision_manager');

        $token = new UsernamePasswordToken('test', null, 'main', [$role]);
        $isAllowed = $decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));

        $this->assertFalse($isAllowed);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}

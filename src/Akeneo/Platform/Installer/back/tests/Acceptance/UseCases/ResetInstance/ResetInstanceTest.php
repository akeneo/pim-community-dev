<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Acceptance\UseCases\ResetInstance;

use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceCommand;
use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceHandler;
use Akeneo\Platform\Installer\Test\Acceptance\FakeServices\FakeDatabasePurger;
use Akeneo\Platform\Installer\Test\Acceptance\FakeServices\FakeFixturesInstaller;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ResetInstanceTest extends KernelTestCase
{
    public function test_it_reset_the_pim_by_keeping_users_user_roles_and_user_groups(): void
    {
        $this->getHandler()->handle(new ResetInstanceCommand());

        $this->assertTablesHaveBeenPurged([
            'akeneo_batch_job_instance',
            'akeneo_batch_job_execution',
            'akeneo_measurement',
            'oro_config',
            'oro_config_value',
            'migration_versions',
            'pim_catalog_product',
            'pim_catalog_product_model',
            'pim_comment_comment',
            'pim_session',
        ]);

        $this->assertTablesHaveNotBeenPurged([
            'acl_classes',
            'acl_entries',
            'acl_object_identities',
            'acl_object_identity_ancestors',
            'acl_security_identities',
            'oro_access_group',
            'oro_access_role',
            'oro_user',
            'oro_user_access_group',
            'oro_user_access_group_role',
            'oro_user_access_role',
        ]);

        $this->assertTrue(
            $this->getFixtureInstaller()->isInstalledWithoutUsersUserGroupsAndUserRoles(),
        );
    }

    private function assertTablesHaveBeenPurged(array $tableNames): void
    {
        $this->getDatabasePurger()->assertTablesHaveBeenPurged($tableNames);
    }

    private function assertTablesHaveNotBeenPurged(array $tableNames): void
    {
        $this->getDatabasePurger()->assertTablesHaveNotBeenPurged($tableNames);
    }

    private function getDatabasePurger(): FakeDatabasePurger
    {
        return self::getContainer()->get('Akeneo\Platform\Installer\Infrastructure\DatabasePurger\DbalPurger');
    }

    private function getFixtureInstaller(): FakeFixturesInstaller
    {
        return self::getContainer()->get('Akeneo\Platform\Installer\Infrastructure\FixtureInstaller\MinimalFixtureInstaller');
    }

    private function getHandler(): ResetInstanceHandler
    {
        return self::getContainer()->get('Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceHandler');
    }
}

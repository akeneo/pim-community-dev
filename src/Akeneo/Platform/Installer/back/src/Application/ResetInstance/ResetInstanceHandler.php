<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\ResetInstance;

use Akeneo\Platform\Installer\Domain\Query\FindTablesInterface;
use Akeneo\Platform\Installer\Domain\Service\DatabasePurgerInterface;
use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;
use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;

class ResetInstanceHandler
{
    private const TABLES_TO_KEEP = [
        'acl_classes',
        'acl_entries',
        'acl_object_identities',
        'acl_object_identity_ancestors',
        'acl_security_identities',
        'pim_configuration',
        'oro_access_group',
        'oro_access_role',
        'oro_user',
        'oro_user_access_group',
        'oro_user_access_group_role',
        'oro_user_access_role',
    ];

    public function __construct(
        private readonly FindTablesInterface $findTables,
        private readonly DatabasePurgerInterface $databasePurger,
        private readonly FixtureInstallerInterface $fixtureInstaller,
        private readonly UserConfigurationResetterInterface $userConfigurationResetter,
    ) {
    }

    public function handle(ResetInstanceCommand $command): void
    {
        $tableNames = $this->findTables->all();
        $tablesToPurge = array_filter(
            $tableNames,
            static fn (string $tableName): bool => !in_array($tableName, self::TABLES_TO_KEEP),
        );

        $this->databasePurger->purge(array_values($tablesToPurge));
        $this->fixtureInstaller->installWithoutUsersUserGroupsAndUserRoles();
        $this->userConfigurationResetter->execute();
    }
}

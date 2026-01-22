<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Command;

/**
 * A ZDD Migration (for Zero Downtime Deployment Migration) is a migration that:
 * - is too long to be executed as a standard Doctrine migration
 * - should not lock any table when running
 * - can be executed in background
 * - can be executed or not, and the code should continue to work.
 *
 * Any change in a ZDD Migration have to be reflected in the database schema. For example, if you add a column in a ZDD
 * Migration, you have to add this column in the Doctrine configuration files, to ensure the new installations doesn't
 * have to execute this migration.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ZddMigration
{
    public function migrate(): void;

    /**
     * As any change in a ZDD Migration have to be reflected in the database schema,
     * we need to provide a way to migrate also without ZDD.
     * It's useful for users running the PIM out of Saas.
     */
    public function migrateNotZdd(): void;

    public function getName(): string;
}

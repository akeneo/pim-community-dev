<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Remove triggers added in the UUID migration which add triggers on foreign uuid column.
 * @see https://github.com/akeneo/pim-community-dev/blob/77be7d26721554834bbbabae39bf6f11a90f77ac/src/Akeneo/Pim/Enrichment/Bundle/Command/MigrateToUuid/MigrateToUuidAddTriggers.php#L15
 */
final class Version_7_0_20220728121643_remove_uuid_triggers extends AbstractMigration
{
    public const TRIGGERS_TO_REMOVE = [
        'pim_versioning_version_uuid_insert',
        'pim_versioning_version_uuid_update',
    ];

    public function getDescription(): string
    {
        return 'Remove UUID triggers';
    }

    public function up(Schema $schema): void
    {
        foreach(self::TRIGGERS_TO_REMOVE as $triggerToRemove) {
            $this->addSql(sprintf('DROP TRIGGER IF EXISTS %s', $triggerToRemove));
        }
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }
}

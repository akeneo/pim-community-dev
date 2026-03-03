<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Install;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallSubscriber implements EventSubscriberInterface
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createIndexMigrationTable',
        ];
    }

    public function createIndexMigrationTable(): void
    {
        $this->dbalConnection->exec(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_index_migration(
                `index_alias` VARCHAR(100) NOT NULL,
                `hash` VARCHAR(100) NOT NULL,
                `values` JSON NOT NULL,
                INDEX migration_index (`index_alias`,`hash`),
                UNIQUE KEY `unique_idx` (`index_alias`,`hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }
}

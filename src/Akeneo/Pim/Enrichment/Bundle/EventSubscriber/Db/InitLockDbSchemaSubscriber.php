<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The table used by the PDOStore of Symfony/Lock is not attached to Doctrine.
 *
 * We need to manually create the table.
 */
class InitLockDbSchemaSubscriber implements EventSubscriberInterface
{
    private $connection;

    public function __construct(Connection $dbalConnection)
    {
        $this->connection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS lock_keys (
    key_id VARCHAR(64) NOT NULL PRIMARY KEY,
    key_token VARCHAR(44) NOT NULL,
    key_expiration INTEGER UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->connection->exec($sql);
    }
}

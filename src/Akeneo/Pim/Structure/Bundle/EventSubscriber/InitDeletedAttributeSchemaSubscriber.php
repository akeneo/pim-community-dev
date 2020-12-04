<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The table used to store blacklisted attributes to avoid recreation before the cleanup job is finished
 *
 * We need to manually create the table.
 */
class InitDeletedAttributeSchemaSubscriber implements EventSubscriberInterface
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

    public function initDbSchema(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS pim_catalog_attribute_blacklist (
    `attribute_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY,
    `cleanup_job_execution_id` INTEGER DEFAULT NULL,
    UNIQUE KEY `searchunique_idx` (`attribute_code`),
    CONSTRAINT `FK_BDE7D0925812C06B` FOREIGN KEY (`cleanup_job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->connection->exec($sql);
    }
}

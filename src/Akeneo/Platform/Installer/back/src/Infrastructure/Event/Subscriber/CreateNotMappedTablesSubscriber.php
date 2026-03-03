<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Event\Subscriber;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateNotMappedTablesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => [
                ['createNotMappedTables', 200],
            ]
        ];
    }

    public function createNotMappedTables(): void
    {
        $sessionTableSql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_session (
                `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
                `sess_data` BLOB NOT NULL,
                `sess_time` INTEGER UNSIGNED NOT NULL,
                `sess_lifetime` INTEGER UNSIGNED NOT NULL
            ) COLLATE utf8mb4_bin, ENGINE = InnoDB
        SQL;
        $this->connection->executeQuery($sessionTableSql);

        $configTableSql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_configuration (
                `code` VARCHAR(128) NOT NULL PRIMARY KEY,
                `values` JSON NOT NULL
            ) COLLATE utf8mb4_unicode_ci, ENGINE = InnoDB
        SQL;
        $this->connection->executeQuery($configTableSql);

        $messengerTableSql = <<<SQL
            CREATE TABLE IF NOT EXISTS messenger_messages (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `queue_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
                `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
                `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
                PRIMARY KEY (`id`),
                KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
                KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
                KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        SQL;
        $this->connection->executeQuery($messengerTableSql);

        $oneTimeTaskTableSql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_one_time_task (
                `code` VARCHAR(100) PRIMARY KEY,
                `status` VARCHAR(100) NOT NULL,
                `start_time` DATETIME,
                `end_time` DATETIME,
                `values` JSON NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

        $this->connection->executeQuery($oneTimeTaskTableSql);
    }
}

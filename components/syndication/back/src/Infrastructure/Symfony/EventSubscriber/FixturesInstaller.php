<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Symfony\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FixturesInstaller implements EventSubscriberInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createSchema'],
        ];
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
            SET foreign_key_checks = 0;
            DROP TABLE IF EXISTS `akeneo_syndication_connected_channel`;

            CREATE TABLE IF NOT EXISTS `akeneo_syndication_connected_channel` (
                `code` VARCHAR(255) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `enabled` BOOLEAN NOT NULL,
                PRIMARY KEY (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            DROP TABLE IF EXISTS `akeneo_syndication_family`;

            CREATE TABLE IF NOT EXISTS `akeneo_syndication_family` (
                `code` VARCHAR(255) NOT NULL,
                `connected_channel_code` VARCHAR(255) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `requirements` JSON NOT NULL,
                PRIMARY KEY (`code`, `connected_channel_code`),
                UNIQUE akeneo_syndication_family_code_family_ux (connected_channel_code, code),
                CONSTRAINT akeneo_syndication_connected_channel_code_foreign_key FOREIGN KEY (connected_channel_code) REFERENCES akeneo_syndication_connected_channel (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            SET foreign_key_checks = 1;
        SQL;

        $this->sqlConnection->executeStatement($sql);
    }
}

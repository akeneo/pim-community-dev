<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallOnboarderSerenityTables implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [InstallerEvents::POST_DB_CREATE => ['installOnboarderSerenityTables']];
    }

    public function installOnboarderSerenityTables(): void
    {
        $this->addSupplierTable();
        $this->addSupplierContributorTable();
    }

    private function addSupplierTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_supplier` (
              `identifier` char(36) NOT NULL,
              `code` varchar(200) NOT NULL,
              `label` varchar(200) NOT NULL,
              PRIMARY KEY (`identifier`),
              CONSTRAINT UC_supplier_code UNIQUE (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addSupplierContributorTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_supplier_contributor` (
              `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
              `email` varchar(255) NOT NULL,
              `supplier_identifier` char(36) NOT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT UC_supplier_contributor_email UNIQUE (`email`),
              CONSTRAINT `supplier_identifier_foreign_key`
                FOREIGN KEY (`supplier_identifier`)
                REFERENCES `akeneo_onboarder_serenity_supplier` (identifier)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }
}

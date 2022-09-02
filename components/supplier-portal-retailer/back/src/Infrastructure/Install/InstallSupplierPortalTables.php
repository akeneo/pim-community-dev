<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallSupplierPortalTables implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [InstallerEvents::POST_DB_CREATE => ['installSupplierPortalTables', 100]];
    }

    public function installSupplierPortalTables(): void
    {
        $this->addSupplierTable();
        $this->addSupplierContributorTable();
        $this->addContributorAccountTable();
        $this->addSupplierFileTable();
    }

    private function addSupplierTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier` (
              `identifier` char(36) NOT NULL,
              `code` varchar(200) NOT NULL,
              `label` varchar(200) NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`identifier`),
              CONSTRAINT UC_supplier_code UNIQUE (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addSupplierContributorTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier_contributor` (
              `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
              `email` varchar(255) NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `supplier_identifier` char(36) NOT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT UC_supplier_contributor_email UNIQUE (`email`),
              CONSTRAINT `supplier_identifier_foreign_key`
                FOREIGN KEY (`supplier_identifier`)
                REFERENCES `akeneo_supplier_portal_supplier` (identifier)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addContributorAccountTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_contributor_account` (
            `id` varchar(36) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) DEFAULT NULL,
            `access_token` varchar(255) DEFAULT NULL,
            `access_token_created_at` DATETIME DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `last_logged_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `UC_contributor_account_email` UNIQUE (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addSupplierFileTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier_file` (
                `identifier` char(36) NOT NULL,
                `original_filename` varchar(255) NOT NULL,
                `path` text NOT NULL,     
                `uploaded_by_contributor` varchar(255) DEFAULT NULL,
                `uploaded_by_supplier` varchar(36) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                `downloaded` BOOLEAN NOT NULL DEFAULT false,
            PRIMARY KEY (`identifier`),
            CONSTRAINT `uploaded_by_supplier_foreign_key`
                FOREIGN KEY (`uploaded_by_supplier`)
                REFERENCES `akeneo_supplier_portal_supplier` (identifier)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }
}

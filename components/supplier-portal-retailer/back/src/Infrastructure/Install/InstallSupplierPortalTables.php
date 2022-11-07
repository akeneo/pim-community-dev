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
        $this->addProductFileTable();
        $this->addProductFileRetailerCommentsTable();
        $this->addProductFileSupplierCommentsTable();
        $this->addProductFileCommentsReadByRetailerTable();
        $this->addProductFileCommentsReadBySupplierTable();
        $this->addProductFileImportedByJobExecutionTable();
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
            `consent` TINYINT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            CONSTRAINT `UC_contributor_account_email` UNIQUE (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addProductFileTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier_product_file` (
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

    private function addProductFileRetailerCommentsTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_retailer_comments` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `author_email` varchar(255) NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `content` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `product_file_retailer_comments_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addProductFileSupplierCommentsTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_supplier_comments` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `author_email` varchar(255) NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `content` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `product_file_supplier_comments_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addProductFileCommentsReadByRetailerTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_comments_read_by_retailer` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `last_read_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `UC_comments_read_by_retailer_product_file_identifier` UNIQUE (`product_file_identifier`),
                CONSTRAINT `comments_read_by_retailer_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addProductFileCommentsReadBySupplierTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_comments_read_by_supplier` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `last_read_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `UC_comments_read_by_supplier_product_file_identifier` UNIQUE (`product_file_identifier`),
                CONSTRAINT `comments_read_by_supplier_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }

    private function addProductFileImportedByJobExecutionTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_imported_by_job_execution` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `job_execution_id` int NOT NULL,
                `job_execution_result` varchar(100) NULL,
                `finished_at` datetime NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `UC_product_file_imported_by_job_execution_job_execution_id` UNIQUE (`job_execution_id`),
                CONSTRAINT `file_imported_by_job_execution_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }
}

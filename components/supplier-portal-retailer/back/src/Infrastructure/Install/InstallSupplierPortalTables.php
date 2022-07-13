<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallSupplierPortalTables implements EventSubscriberInterface
{
    public const SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA = [
        'code' => 'supplier_portal_xlsx_supplier_import',
        'label' => 'Supplier Portal XLSX Supplier Import',
        'job_name' => 'supplier_portal_xlsx_supplier_import',
        'connector' => 'Supplier Portal',
        'raw_parameters' => 'a:0:{}',
        'type' => 'import',
    ];

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
        $this->addSupplierPortalXlsxSupplierImportJob();
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

    private function addSupplierPortalXlsxSupplierImportJob(): void
    {
        if ($this->SupplierPortalXlsxSupplierImportJobExists()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['code'],
                'label' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['label'],
                'connector' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['connector'],
                'rawParameters' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['raw_parameters'],
                'type' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['type'],
            ],
        );
    }

    private function SupplierPortalXlsxSupplierImportJobExists(): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
            ->connection
            ->executeQuery($sql, ['code' => self::SUPPLIER_PORTAL_XLSX_SUPPLIER_IMPORT_JOB_DATA['code']])
            ->fetchOne()
        ;
    }

    private function addSupplierFileTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier_file` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `filename` varchar(255) NOT NULL,
                `path` varchar(255) NOT NULL,     
                `uploaded_by_contributor` varchar(36) DEFAULT NULL,
                `uploaded_by_supplier` varchar(36) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                `downloaded_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `uploaded_by_supplier_foreign_key`
                FOREIGN KEY (`uploaded_by_supplier`)
                REFERENCES `akeneo_supplier_portal_supplier` (identifier)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement($sql);
    }
}

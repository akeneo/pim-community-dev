<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallOnboarderSerenityTables implements EventSubscriberInterface
{
    public const ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA = [
        'code' => 'onboarder_serenity_xlsx_supplier_import',
        'label' => 'Onboarder Serenity XLSX Supplier Import',
        'job_name' => 'onboarder_serenity_xlsx_supplier_import',
        'connector' => 'Onboarder Serenity',
        'raw_parameters' => 'a:0:{}',
        'type' => 'import',
    ];

    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [InstallerEvents::POST_DB_CREATE => ['installOnboarderSerenityTables', 100]];
    }

    public function installOnboarderSerenityTables(): void
    {
        $this->addSupplierTable();
        $this->addSupplierContributorTable();
        $this->addContributorAccountTable();
        $this->addOnboarderSerenityXlsxSupplierImportJob();
    }

    private function addSupplierTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_supplier` (
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
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_supplier_contributor` (
              `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
              `email` varchar(255) NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

    private function addContributorAccountTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_contributor_account` (
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

    private function addOnboarderSerenityXlsxSupplierImportJob(): void
    {
        if ($this->onboarderSerenityXlsxSupplierImportJobExists()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['code'],
                'label' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['label'],
                'connector' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['connector'],
                'rawParameters' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['raw_parameters'],
                'type' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['type'],
            ],
        );
    }

    private function onboarderSerenityXlsxSupplierImportJobExists(): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
            ->connection
            ->executeQuery($sql, ['code' => self::ONBOARDER_SERENITY_XLSX_SUPPLIER_IMPORT_JOB_DATA['code']])
            ->fetchOne()
        ;
    }
}

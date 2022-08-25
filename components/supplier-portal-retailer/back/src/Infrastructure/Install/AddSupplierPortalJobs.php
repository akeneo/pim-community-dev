<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddSupplierPortalJobs implements EventSubscriberInterface
{
    private const JOB_CONFIGURATIONS = [
        'supplier_portal_xlsx_supplier_import' => [
            'code' => 'supplier_portal_xlsx_supplier_import',
            'label' => 'Supplier Portal XLSX Supplier Import',
            'job_name' => 'supplier_portal_xlsx_supplier_import',
            'connector' => 'Supplier Portal',
            'raw_parameters' => 'a:0:{}',
            'type' => 'import',
        ],
        'supplier_portal_supplier_product_files_clean' => [
            'code' => 'supplier_portal_supplier_product_files_clean',
            'label' => 'Clean old supplier product files',
            'job_name' => 'supplier_portal_supplier_product_files_clean',
            'connector' => 'Supplier Portal',
            'raw_parameters' => 'a:0:{}',
            'type' => 'scheduled_job',
        ],
    ];

    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [InstallerEvents::POST_LOAD_FIXTURES => [['addSupplierPortalJobs'],],];
    }

    public function addSupplierPortalJobs(): void
    {
        $this->addCleanSupplierProductFilesJob();
        $this->addSupplierPortalXlsxSupplierImportJob();
    }

    private function addSupplierPortalXlsxSupplierImportJob(): void
    {
        if ($this->jobInstanceExists('supplier_portal_xlsx_supplier_import')) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO `akeneo_batch_job_instance` (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => 'supplier_portal_xlsx_supplier_import',
                'label' => self::JOB_CONFIGURATIONS['supplier_portal_xlsx_supplier_import']['label'],
                'connector' => self::JOB_CONFIGURATIONS['supplier_portal_xlsx_supplier_import']['connector'],
                'rawParameters' => self::JOB_CONFIGURATIONS['supplier_portal_xlsx_supplier_import']['raw_parameters'],
                'type' => self::JOB_CONFIGURATIONS['supplier_portal_xlsx_supplier_import']['type'],
            ],
        );
    }

    private function addCleanSupplierProductFilesJob(): void
    {
        if ($this->jobInstanceExists('supplier_portal_supplier_product_files_clean')) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO `akeneo_batch_job_instance` (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => 'supplier_portal_supplier_product_files_clean',
                'label' => self::JOB_CONFIGURATIONS['supplier_portal_supplier_product_files_clean']['label'],
                'connector' => self::JOB_CONFIGURATIONS['supplier_portal_supplier_product_files_clean']['connector'],
                'rawParameters' => self::JOB_CONFIGURATIONS['supplier_portal_supplier_product_files_clean']['raw_parameters'],
                'type' => self::JOB_CONFIGURATIONS['supplier_portal_supplier_product_files_clean']['type'],
            ],
        );
    }

    private function jobInstanceExists(string $jobCode): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
                ->connection
                ->executeQuery($sql, ['code' => $jobCode])
                ->fetchOne();
    }
}

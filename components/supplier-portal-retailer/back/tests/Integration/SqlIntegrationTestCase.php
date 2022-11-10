<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests.
 * It should be used for testing the SQL implementation of query functions and repositories.
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->connection = $this->get('doctrine.dbal.default_connection');

        $this->connection->executeStatement(<<<SQL
            DELETE FROM `akeneo_supplier_portal_supplier`;
            DELETE FROM `akeneo_supplier_portal_supplier_contributor`;
            DELETE FROM `akeneo_supplier_portal_supplier_product_file`;
            DELETE FROM `akeneo_supplier_portal_product_file_retailer_comments`;
            DELETE FROM `akeneo_supplier_portal_product_file_supplier_comments`;
            DELETE FROM `akeneo_supplier_portal_product_file_comments_read_by_retailer`;
            DELETE FROM `akeneo_supplier_portal_product_file_comments_read_by_supplier`;
            DELETE FROM `akeneo_supplier_portal_product_file_imported_by_job_execution`;
            DELETE FROM `akeneo_batch_job_execution`;
        SQL);

        $this->addSupplierPortalXlsxSupplierImportJob();
    }

    protected function get(string $service): ?object
    {
        return static::getContainer()->get($service);
    }

    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();
        $filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
        $fileSystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        foreach ($fileSystem->listContents('./') as $supplierDirectory) {
            $fileSystem->deleteDirectory($supplierDirectory->path());
        }

        $this->ensureKernelShutdown();
    }

    private function addSupplierPortalXlsxSupplierImportJob(): void
    {
        if ($this->supplierPortalXlsxSupplierImportJobExists()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => 'supplier_portal_xlsx_supplier_import',
                'label' => 'Supplier Portal XLSX Supplier Import',
                'connector' => 'Supplier Portal',
                'rawParameters' => 'a:0:{}',
                'type' => 'import',
            ],
        );
    }

    private function supplierPortalXlsxSupplierImportJobExists(): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
                ->connection
                ->executeQuery($sql, ['code' => 'supplier_portal_xlsx_supplier_import'])
                ->fetchOne()
            ;
    }
}

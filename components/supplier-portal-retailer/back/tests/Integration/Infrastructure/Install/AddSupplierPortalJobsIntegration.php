<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Install;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Install\AddSupplierPortalJobs;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class AddSupplierPortalJobsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itAddsSupplierPortalJobs(): void
    {
        $this->get(AddSupplierPortalJobs::class)->addSupplierPortalJobs();

        static::assertSame(2, $this->countSupplierPortalJobs());
        static::assertTrue($this->supplierPortalJobExists('supplier_portal_xlsx_supplier_import'));
        static::assertTrue($this->supplierPortalJobExists('supplier_portal_supplier_product_files_clean'));
    }

    private function countSupplierPortalJobs(): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE connector = :connector
        SQL;

        return (int) $this
            ->connection
            ->executeQuery($sql, ['connector' => 'Supplier Portal',])
            ->fetchOne();
    }

    private function supplierPortalJobExists(string $jobCode): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return (bool) (int) $this
            ->connection
            ->executeQuery($sql, ['code' => $jobCode,])
            ->fetchOne();
    }
}

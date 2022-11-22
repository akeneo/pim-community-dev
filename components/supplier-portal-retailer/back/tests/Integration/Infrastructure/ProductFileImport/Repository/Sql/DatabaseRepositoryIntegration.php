<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileImport\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')->build(),
        );
    }

    /** @test */
    public function itCreatesAProductFileImport(): void
    {
        $this->createProductFile('26a20c23-af44-4c34-8f06-5ea4eb5fe700');
        $productFile = $this->createProductFile('44ce8069-8da1-4986-872f-311737f46f02');

        $sut = $this->get(ProductFileImportRepository::class);
        $productFileImport = ProductFileImport::start($productFile, 666);
        $sut->save($productFileImport);

        $result = $this->get(Connection::class)
            ->executeQuery(
                <<<SQL
                SELECT *
                FROM akeneo_supplier_portal_product_file_imported_by_job_execution
                WHERE product_file_identifier = '44ce8069-8da1-4986-872f-311737f46f02'
            SQL
            )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertSame('44ce8069-8da1-4986-872f-311737f46f02', $result['product_file_identifier']);
        $this->assertSame(666, (int) $result['job_execution_id']);
        $this->assertNull($result['finished_at']);
    }

    /** @test */
    public function itUpdatesAProductFileImport(): void
    {
        $this->createProductFile('26a20c23-af44-4c34-8f06-5ea4eb5fe700');
        $productFile = $this->createProductFile('44ce8069-8da1-4986-872f-311737f46f02');

        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_product_file_imported_by_job_execution (
                product_file_identifier,
                job_execution_id,
                import_status,
                finished_at
            ) VALUES (
                '44ce8069-8da1-4986-872f-311737f46f02',
                '12',
                'failed',
                '2022-12-25 00:01:00'
            )
SQL;
        $this->get(Connection::class)->executeQuery($sql);

        $sut = $this->get(ProductFileImportRepository::class);
        $productFileImport = ProductFileImport::start($productFile, 666);
        $sut->save($productFileImport);

        $result = $this->get(Connection::class)
            ->executeQuery(
                <<<SQL
                SELECT *
                FROM akeneo_supplier_portal_product_file_imported_by_job_execution
                WHERE product_file_identifier = '44ce8069-8da1-4986-872f-311737f46f02'
            SQL
            )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertSame(666, (int) $result['job_execution_id']);
        $this->assertSame('in_progress', $result['import_status']);
        $this->assertNull($result['finished_at']);
    }

    /** @test */
    public function itFindsAProductFileImportByJobExecutionId(): void
    {
        $productFile = $this->createProductFile('44ce8069-8da1-4986-872f-311737f46f02');

        $sut = $this->get(ProductFileImportRepository::class);
        $productFileImport = ProductFileImport::start($productFile, 666);
        $sut->save($productFileImport);

        $result = $sut->findByImportExecutionId(666);
        $this->assertSame(666, (int) $result->importExecutionId());
        $this->assertSame('44ce8069-8da1-4986-872f-311737f46f02', $result->productFileIdentifier());
    }

    /** @test */
    public function itReturnsNullIfProductFileImportDoesNotExist(): void
    {
        $this->assertNull($this->get(ProductFileImportRepository::class)->findByImportExecutionId(666));
    }

    private function createProductFile(string $identifier): ProductFile
    {
        $productFile = (new ProductFileBuilder)
            ->withIdentifier($identifier)
            ->uploadedBySupplier(
                new Supplier(
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'supplier_code',
                    'Supplier label',
                ),
            )
            ->build();

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save($productFile);

        return $productFile;
    }
}

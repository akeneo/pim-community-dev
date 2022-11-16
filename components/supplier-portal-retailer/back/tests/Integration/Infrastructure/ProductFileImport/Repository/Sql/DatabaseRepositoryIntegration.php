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
    public function itSavesAProductFileImport(): void
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
        $this->assertSame($result['product_file_identifier'], '44ce8069-8da1-4986-872f-311737f46f02');
        $this->assertSame((int) $result['job_execution_id'], 666);
        $this->assertNull($result['finished_at']);
    }

    /** @test */
    public function itFindsAProductFileImportByJobExecutionId(): void
    {
        $productFile = $this->createProductFile('44ce8069-8da1-4986-872f-311737f46f02');

        $sut = $this->get(ProductFileImportRepository::class);
        $productFileImport = ProductFileImport::start($productFile, 666);
        $sut->save($productFileImport);

        $result = $sut->findByJobExecutionId(666);
        $this->assertSame($result->importExecutionId(), 666);
        $this->assertSame($result->productFileIdentifier(), '44ce8069-8da1-4986-872f-311737f46f02');
    }

    /** @test */
    public function itReturnsNullIfProductFileImportDoesNotExist(): void
    {
        $this->assertNull($this->get(ProductFileImportRepository::class)->findByJobExecutionId(666));
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

<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseListProductFilesForSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        $this->createSuppliers();
        $this->createContributors();

        static::assertEmpty(($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7'));
    }

    /** @test */
    public function itGetsTheLatestTwentyFiveProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        $this->createSuppliers();
        $this->createContributors();
        $this->createProductFiles();

        $sut = $this->get(ListProductFilesForSupplier::class);

        $supplierProductFiles = ($sut)('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        $expectedProductFilenames = [];
        for ($i = 0; 25 > $i; $i++) {
            $expectedProductFilenames[] = sprintf('products_%d.xlsx', $i+1);
        }

        static::assertEqualsCanonicalizing(
            $expectedProductFilenames,
            array_map(
                fn (ProductFile $supplierProductFile) => $supplierProductFile->originalFilename,
                $supplierProductFiles,
            ),
        );
    }

    private function createSuppliers(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label) 
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier1', 'Supplier 1'),
                   ('951c7717-8316-42e7-b053-61f265507178', 'supplier2', 'Supplier 2');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }

    private function createProductFiles(): void
    {
        $this->get(Connection::class)->executeStatement(
            <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier_product_file (
                identifier, 
                original_filename, 
                path, 
                uploaded_by_contributor, 
                uploaded_by_supplier, 
                uploaded_at
            ) VALUES (
                :identifier,
                :original_filename,
                :path,
                :contributorEmail,
                :supplierIdentifier,
                :uploadedAt
            )
        SQL,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'original_filename' => 'products_file_from_another_supplier.xlsx',
                'path' => sprintf(
                    'supplier2/%s-products_file_from_another_supplier.xlsx',
                    Uuid::uuid4()->toString(),
                ),
                'contributorEmail' => 'contributor-belonging-to-another-supplier@example.com',
                'supplierIdentifier' => '951c7717-8316-42e7-b053-61f265507178',
                'uploadedAt' => (new \DateTimeImmutable())->add(
                    \DateInterval::createFromDateString(sprintf('1 year')),
                )->format('Y-m-d H:i:s'),
            ],
        );

        for ($i = 0; 30 > $i; $i++) {
            $sql = <<<SQL
                INSERT INTO akeneo_supplier_portal_supplier_product_file (
                    identifier, 
                    original_filename, 
                    path, 
                    uploaded_by_contributor, 
                    uploaded_by_supplier, 
                    uploaded_at
                ) VALUES (
                    :identifier,
                    :originalFilename,
                    :path,
                    :contributorEmail,
                    :supplierIdentifier,
                    :uploadedAt
                )
            SQL;

            $this->get(Connection::class)->executeStatement(
                $sql,
                [
                    'identifier' => Uuid::uuid4()->toString(),
                    'originalFilename' => sprintf('products_%d.xlsx', $i+1),
                    'path' => sprintf('supplier1/%s-products_1.xlsx', Uuid::uuid4()->toString()),
                    'contributorEmail' => $i % 2 ? 'contributor1@example.com' : 'contributor2@example.com',
                    'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'uploadedAt' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(sprintf('%d minutes', 30 - $i)),
                    )->format('Y-m-d H:i:s'),
                ],
            );
        }
    }

    private function createContributors(): void
    {
        $this->get(Connection::class)->executeStatement(
            <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier_contributor (id, email, supplier_identifier)
                VALUES (1, 'contributor1@example.com', 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7'),
                       (2, 'contributor2@example.com', 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
           ;
        SQL
        );
    }
}

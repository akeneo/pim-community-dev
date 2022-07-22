<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFiles;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        static::assertEmpty(
            ($this->get(GetProductFiles::class))(
                Identifier::fromString('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
            ),
        );
    }

    /** @test */
    public function itGetsTheLatestTwentyFiveProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        $this->createSuppliers();
        $this->createProductFiles();

        $sut = $this->get(GetProductFiles::class);

        $supplierIdentifier = Identifier::fromString('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        $supplierProductFiles = ($sut)($supplierIdentifier);

        $expectedProductFilenames = [];
        for ($i = 0; 25 > $i; $i++) {
            $expectedProductFilenames[] = sprintf('products_%d.xlsx', $i+1);
        }

        static::assertSame(
            $expectedProductFilenames,
            array_map(
                fn (SupplierFile $supplierProductFile) => $supplierProductFile->originalFilename,
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
            INSERT INTO akeneo_supplier_portal_supplier_file (
                identifier, 
                filename, 
                path, 
                uploaded_by_contributor, 
                uploaded_by_supplier, 
                uploaded_at
            ) VALUES (
                :identifier,
                :filename,
                :path,
                :contributorEmail,
                :supplierIdentifier,
                :uploadedAt
            )
        SQL,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'filename' => sprintf('products_file_from_another_supplier.xlsx'),
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
                INSERT INTO akeneo_supplier_portal_supplier_file (
                    identifier, 
                    filename, 
                    path, 
                    uploaded_by_contributor, 
                    uploaded_by_supplier, 
                    uploaded_at
                ) VALUES (
                    :identifier,
                    :filename,
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
                    'filename' => sprintf('products_%d.xlsx', $i+1),
                    'path' => sprintf('supplier1/%s-products_1.xlsx', Uuid::uuid4()->toString()),
                    'contributorEmail' => $i % 2 ? 'contributor1@example.com' : 'contributor2@example.com',
                    'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'uploadedAt' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(sprintf('%d seconds', 30 - $i)),
                    )->format('Y-m-d H:i:s'),
                ],
            );
        }
    }
}

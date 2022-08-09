<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilePathAndFileNameIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoProductFileForTheGivenIdentifier(): void
    {
        static::assertNull(
            ($this->get(GetProductFilePathAndFileName::class))(
                Identifier::fromString('64cde636-97db-4bb1-9279-2f13b1d2e9da')
            ),
        );
    }

    /** @test */
    public function itGetsTheFilenameAndThePathFromAFileIdentifier(): void
    {
        $this->createSupplier();
        $this->createProductFiles();

        $productFile = ($this->get(GetProductFilePathAndFileName::class))(
            Identifier::fromString('e6949a68-177e-4451-ad62-4debf90df079')
        );

        static::assertSame(
            'products_file_1.xlsx',
            $productFile->originalFilename,
        );
        static::assertSame(
            'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
            $productFile->path,
        );
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label)
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier1', 'Supplier 1');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }

    private function createProductFiles(): void
    {
        $this->get(Connection::class)->executeStatement(
            <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier_file (
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
        SQL,
            [
                'identifier' => 'e6949a68-177e-4451-ad62-4debf90df079',
                'originalFilename' => 'products_file_1.xlsx',
                'path' => 'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
                'contributorEmail' => 'contributor@example.com',
                'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        );

        $this->get(Connection::class)->executeStatement(
            <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier_file (
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
        SQL,
            [
                'identifier' => 'ef8a6fd2-18b4-4fc2-af07-839d7d039e6a',
                'originalFilename' => 'products_file_2.xlsx',
                'path' => sprintf(
                    'supplier1/%s-products_file_2.xlsx',
                    Uuid::uuid4()->toString(),
                ),
                'contributorEmail' => 'contributor@example.com',
                'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        );
    }
}

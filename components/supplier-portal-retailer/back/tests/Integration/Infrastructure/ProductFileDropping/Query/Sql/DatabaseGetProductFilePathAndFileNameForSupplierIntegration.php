<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileNameForSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDoesNotGetTheFilenameAndThePathIfTheProductFileIdentifierHasNotBeenUploadedByOneOfTheContributorsOfTheSupplierTheContributorConnectedBelongsTo(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('bb2241e8-5242-4dbb-9d20-5e4e38514566', 'supplier_2', 'Supplier 2');
        $this->createProductFile(
            'ad54830a-aeae-4b57-8313-679a2327c5f7',
            'path/to/products_file_1.xlsx',
            'products_file_1.xlsx',
        );
        $this->createProductFile(
            'd943f1dd-adc7-440d-9b4d-5a4b71073e04',
            'bb2241e8-5242-4dbb-9d20-5e4e38514566',
            'supplier2/a8051bf5-c948-4f78-862c-71aa4268f5f4-products_file_2.xlsx',
            'products_file_2.xlsx',
            'contributor+supplier2@example.com',
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileNameForSupplier::class))(
                'ad54830a-aeae-4b57-8313-679a2327c5f7'
            )
        );
    }

    /** @test */
    public function itGetsTheFilenameAndThePathForFilesIOrMyTeammatesDropped(): void
    {

    }

    /** @test */
    public function itGetsTheFilenameAndThePathFromAProductFileIdentifier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        $this->createProductFile(
            'ad54830a-aeae-4b57-8313-679a2327c5f7',
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
            'products_file_1.xlsx',
            'contributor@example.com',
        );

        $productFile = ($this->get(GetProductFilePathAndFileNameForSupplier::class))('ad54830a-aeae-4b57-8313-679a2327c5f7'));

        static::assertSame(
            'products_file_1.xlsx',
            $productFile->originalFilename,
        );
        static::assertSame(
            'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
            $productFile->path,
        );
    }

    private function createSupplier(string $identifier, string $code, string $label): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'code' => $code,
                'label' => $label,
            ],
        );
    }

    private function createProductFile(
        string $productFileIdentifier,
        string $path,
        string $filename,
    ): void {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                downloaded
            )
            VALUES (:identifier, :originalFilename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt, :downloaded)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $productFileIdentifier,
                'originalFilename' => $filename,
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'downloaded' => 0,
            ],
        );
    }
}

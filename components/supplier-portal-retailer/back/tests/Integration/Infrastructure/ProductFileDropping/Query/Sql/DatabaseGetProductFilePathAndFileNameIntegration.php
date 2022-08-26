<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilePathAndFileNameIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsThePathOfASupplierFile(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $identifier = $this->createProductFile('path/to/file/file.xlsx', 'file.xlsx', new \DateTimeImmutable());

        $productFilePathAndFileName = $this->get(GetProductFilePathAndFileName::class)($identifier);

        $this->assertSame(
            'path/to/file/file.xlsx',
            $productFilePathAndFileName->path,
        );

        $this->assertSame(
            'file.xlsx',
            $productFilePathAndFileName->originalFilename,
        );
    }

    /** @test */
    public function itReturnsNullIfTheFileDoesNotExist(): void
    {
        $this->assertNull($this->get(GetProductFilePathAndFileName::class)('unknown-file'));
    }

    /** @test */
    public function itDoesNotGetTheFilenameAndThePathIfTheSupplierFileIdentifierHasNotBeenUploadedByOneOfTheContributorsOfTheSupplierTheContributorConnectedBelongsTo(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('bb2241e8-5242-4dbb-9d20-5e4e38514566', 'supplier_2', 'Supplier 2');
        $this->createContributor(
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f00',
            1,
        );
        $this->createContributor(
            'contributor+supplier2@example.com',
            'bb2241e8-5242-4dbb-9d20-5e4e38514566',
            2,
        );
        $this->createProductFile(
            'ad54830a-aeae-4b57-8313-679a2327c5f7',
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
            'products_file_1.xlsx',
            'contributor@example.com',
        );
        $this->createProductFile(
            'd943f1dd-adc7-440d-9b4d-5a4b71073e04',
            'bb2241e8-5242-4dbb-9d20-5e4e38514566',
            'supplier2/a8051bf5-c948-4f78-862c-71aa4268f5f4-products_file_2.xlsx',
            'products_file_2.xlsx',
            'contributor+supplier2@example.com',
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileName::class))(
                Identifier::fromString('ad54830a-aeae-4b57-8313-679a2327c5f7'),
                'contributor+supplier2@example.com'
            ),
        );
    }

    /** @test */
    public function itGetsTheFilenameAndThePathFromAFileIdentifier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createContributor(
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f00',
            1,
        );

        $this->createProductFile(
            'ad54830a-aeae-4b57-8313-679a2327c5f7',
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier1/e6949a68-177e-4451-ad62-4debf90df079-products_file_1.xlsx',
            'products_file_1.xlsx',
            'contributor@example.com',
        );

        $productFile = ($this->get(GetProductFilePathAndFileName::class))(
            Identifier::fromString('ad54830a-aeae-4b57-8313-679a2327c5f7'),
            'contributor@example.com'
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
        string $path,
        string $filename,
        \DateTimeImmutable $uploadedAt,
    ): string {
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
        $identifier = Uuid::uuid4()->toString();

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'originalFilename' => $filename,
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => 0,
            ],
        );

        return $identifier;
    }
}

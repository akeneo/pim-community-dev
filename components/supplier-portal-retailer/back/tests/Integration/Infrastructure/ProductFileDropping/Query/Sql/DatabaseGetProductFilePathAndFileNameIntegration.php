<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetProductFilePathAndFileName;
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

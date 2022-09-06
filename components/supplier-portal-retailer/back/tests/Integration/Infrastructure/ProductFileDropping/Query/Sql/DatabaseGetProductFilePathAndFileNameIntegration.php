<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileNameIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfTheProductFileDoesNotExist(): void
    {
        $this->assertNull($this->get(GetProductFilePathAndFileName::class)('unknown-file'));
    }

    /** @test */
    public function itGetsTheFilenameAndThePathFromAFileIdentifier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createProductFile(
            'ad54830a-aeae-4b57-8313-679a2327c5f7',
            'path/to/products_file_1.xlsx',
            'products_file_1.xlsx',
        );

        $productFile = ($this->get(GetProductFilePathAndFileName::class))('ad54830a-aeae-4b57-8313-679a2327c5f7');

        static::assertSame(
            'products_file_1.xlsx',
            $productFile->originalFilename,
        );
        static::assertSame(
            'path/to/products_file_1.xlsx',
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
            INSERT INTO `akeneo_supplier_portal_supplier_product_file` (
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

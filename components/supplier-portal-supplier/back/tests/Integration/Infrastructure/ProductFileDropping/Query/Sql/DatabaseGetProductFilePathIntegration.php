<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePath;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilePathIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsThePathOfAProductFile(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $identifier = $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable());

        $this->assertSame(
            'path/to/file/file.xlsx',
            (string) $this->get(GetProductFilePath::class)(Identifier::fromString($identifier)),
        );
    }

    /** @test */
    public function itReturnsNullIfTheFileDoesNotExist(): void
    {
        $this->assertNull(
            $this->get(GetProductFilePath::class)(
                Identifier::fromString('367df70b-f118-4329-9b42-8d0dc11361d7')
            ),
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

    private function createSupplierFile(string $path, \DateTimeImmutable $uploadedAt, bool $downloaded = false): string
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at, downloaded)
            VALUES (:identifier, :originalFilename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt, :downloaded)
        SQL;
        $identifier = Uuid::uuid4()->toString();

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'originalFilename' => 'file.xlsx',
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => (int) $downloaded,
            ],
        );

        return $identifier;
    }
}

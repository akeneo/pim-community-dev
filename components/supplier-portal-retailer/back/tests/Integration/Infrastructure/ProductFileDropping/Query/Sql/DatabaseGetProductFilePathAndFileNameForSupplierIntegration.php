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
        $this->createContributor('contributor+supplier1@example.com', '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createContributor('contributor+supplier2@example.com', 'bb2241e8-5242-4dbb-9d20-5e4e38514566');
        $this->createProductFile(
            'de42d046-fd5a-4254-b5d5-bda2cb6543d2',
            'path/to/products_file_of_another_supplier.xlsx',
            'products_file_of_another_supplier.xlsx',
            'contributor+supplier2@example.com',
            'bb2241e8-5242-4dbb-9d20-5e4e38514566',
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileNameForSupplier::class))(
                'de42d046-fd5a-4254-b5d5-bda2cb6543d2',
                '44ce8069-8da1-4986-872f-311737f46f00',
            ),
        );
    }

    /** @test */
    public function itGetsTheFilenameAndThePathForProductFilesIOrMyTeammatesDropped(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createContributor('contributor1+supplier1@example.com', '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createContributor('contributor2+supplier1@example.com', '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createProductFile(
            'd5852c6a-a418-4d53-b744-8934e2d9f0fb',
            'path/to/products_file_contributor_2.xlsx',
            'products_file_contributor_2.xlsx',
            'contributor2+supplier1@example.com',
            '44ce8069-8da1-4986-872f-311737f46f00',
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileNameForSupplier::class))(
                'de42d046-fd5a-4254-b5d5-bda2cb6543d2',
                '44ce8069-8da1-4986-872f-311737f46f00',
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

    private function createProductFile(
        string $productFileIdentifier,
        string $path,
        string $filename,
        string $contributorEmail,
        string $supplierIdentifier,
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
                'contributorEmail' => $contributorEmail,
                'supplierIdentifier' => $supplierIdentifier,
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'downloaded' => 0,
            ],
        );
    }

    private function createContributor(string $email, string $supplierIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_contributor` (email, supplier_identifier)
            VALUES (:email, :supplierIdentifier)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'email' => $email,
                'supplierIdentifier' => $supplierIdentifier,
            ],
        );
    }
}

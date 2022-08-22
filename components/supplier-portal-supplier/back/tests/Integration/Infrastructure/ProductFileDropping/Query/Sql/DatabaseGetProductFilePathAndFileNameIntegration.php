<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileNameIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoProductFileForTheGivenIdentifier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createContributor(
            'b98d8e88-b8e0-48f6-8be3-06b329318420',
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f00',
            1,
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileName::class))(
                Identifier::fromString('64cde636-97db-4bb1-9279-2f13b1d2e9da'),
                'contributor@example.com'
            ),
        );
    }

    /** @test */
    public function itDoesNotGetTheFilenameAndThePathIfTheSupplierFileIdentifierHasNotBeenUploadedByOneOfTheContributorsOfTheSupplierTheContributorConnectedBelongsTo(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('bb2241e8-5242-4dbb-9d20-5e4e38514566', 'supplier_2', 'Supplier 2');
        $this->createContributor(
            'b98d8e88-b8e0-48f6-8be3-06b329318420',
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f00',
            1,
        );
        $this->createContributor(
            '11f8b2e3-c15d-470c-9fcb-a8bdab0990c8',
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
            'b98d8e88-b8e0-48f6-8be3-06b329318420',
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
        string $identifier,
        string $supplierIdentifier,
        string $path,
        string $filename,
        string $contributorEmail,
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
                'identifier' => $identifier,
                'originalFilename' => $filename,
                'path' => $path,
                'contributorEmail' => $contributorEmail,
                'supplierIdentifier' => $supplierIdentifier,
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'downloaded' => 0,
            ],
        );
    }

    private function createContributor(
        string $contributorIdentifier,
        string $contributorEmail,
        string $supplierIdentifier,
        int $contributorId,
    ): void {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_contributor_account` (
                id,
                email,
                created_at
            )
            VALUES (:id, :email,:createdAt)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'id' => $contributorIdentifier,
                'email' => $contributorEmail,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        );

        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_contributor` (
                id,
                email,
                created_at,
                supplier_identifier
            )
            VALUES (:contributorId, :email,:createdAt, :supplierIdentifier)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'contributorId' => $contributorId,
                'email' => $contributorEmail,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'supplierIdentifier' => $supplierIdentifier,
            ],
        );
    }
}

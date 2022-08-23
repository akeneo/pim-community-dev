<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesASupplierFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(SupplierFileRepository::class);
        $supplierFile = SupplierFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'contributor@example.com',
            new Supplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'los_pollos_hermanos', 'Los Pollos Hermanos'),
        );
        $repository->save($supplierFile);

        $savedSupplierFile = $this->findSupplierFile('product-file.xlsx');

        $this->assertSame($supplierFile->originalFilename(), $savedSupplierFile['original_filename']);
        $this->assertSame($supplierFile->path(), $savedSupplierFile['path']);
        $this->assertSame($supplierFile->contributorEmail(), $savedSupplierFile['uploaded_by_contributor']);
        $this->assertSame($supplierFile->supplierIdentifier(), $savedSupplierFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedSupplierFile['downloaded']);
    }

    /** @test */
    public function itDeletesOldFiles(): void
    {
        $this->createSupplier();
        $this->createSupplierProductFiles();

        $supplierProductFileRepository = $this->get(SupplierFileRepository::class);
        $supplierProductFileRepository->deleteOld();
        $supplierProductFilenames = $this->findSupplierProductFiles();

        static::assertEqualsCanonicalizing([
            ['original_filename' => 'file1.xlsx'],
            ['original_filename' => 'file2.xlsx'],
        ], $supplierProductFilenames);
    }

    private function findSupplierProductFiles(): array
    {
        $sql = <<<SQL
            SELECT original_filename
            FROM `akeneo_supplier_portal_supplier_file`
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    private function findSupplierFile(string $originalFilename): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE original_filename = :original_filename
        SQL;

        $supplierFile = $this->get(Connection::class)
            ->executeQuery($sql, ['original_filename' => $originalFilename])
            ->fetchAssociative();

        return $supplierFile ?: null;
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label) 
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier-1', 'Supplier 1');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }

    private function createSupplierProductFiles(): void
    {
        $connection = $this->get(Connection::class);

        for ($i = 0; 3 > $i; $i++) {
            $sql = <<<SQL
                INSERT INTO akeneo_supplier_portal_supplier_file (
                    identifier,
                    original_filename,
                    path,
                    uploaded_by_contributor,
                    uploaded_by_supplier,
                    uploaded_at
                )
                VALUES (
                    :identifier,
                    :filename,
                    :filepath,
                    'contributor@example.com',
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    :uploadedAt
                )
            SQL;

            $connection->executeStatement(
                $sql,
                [
                    'identifier' => Uuid::uuid4()->toString(),
                    'filename' => sprintf('file%d.xlsx', $i + 1),
                    'filepath' => sprintf('supplier-1/file%d.xlsx', $i + 1),
                    'uploadedAt' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(sprintf('-%d days', ($i + 1) * 40)),
                    )->format('Y-m-d H:i:s'),
                ],
            );
        }
    }
}

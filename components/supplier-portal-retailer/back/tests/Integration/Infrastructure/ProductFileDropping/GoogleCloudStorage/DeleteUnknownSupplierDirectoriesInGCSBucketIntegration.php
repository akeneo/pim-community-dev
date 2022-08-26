<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\DeleteUnknownSupplierDirectoriesInGCSBucket;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DeleteUnknownSupplierDirectoriesInGCSBucketIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDeletesUnknownSupplierDirectoriesInGCSBucket(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('6563b4b1-71c5-420c-bf6c-772108bbbc92', 'supplier_2', 'Supplier 2');

        $filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->createDirectory('supplier_1');
        $filesystem->createDirectory('supplier_2');
        $filesystem->createDirectory('unknown_supplier_code');

        $this->get(DeleteUnknownSupplierDirectoriesInGCSBucket::class)();

        $supplierDirectories = [];
        foreach ($filesystem->listContents('./') as $supplierDirectory) {
            $supplierDirectories[] = $supplierDirectory->path();
        }

        static::assertEqualsCanonicalizing(['supplier_1', 'supplier_2'], $supplierDirectories);
    }

    /** @test */
    public function itDoesNotDeleteAnySupplierDirectoriesIfAllTheSuppliersExistInDatabase(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('6563b4b1-71c5-420c-bf6c-772108bbbc92', 'supplier_2', 'Supplier 2');

        $filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->createDirectory('supplier_1');
        $filesystem->createDirectory('supplier_2');

        $this->get(DeleteUnknownSupplierDirectoriesInGCSBucket::class)();

        $supplierDirectories = [];
        foreach ($filesystem->listContents('./') as $supplierDirectory) {
            $supplierDirectories[] = $supplierDirectory->path();
        }

        static::assertEqualsCanonicalizing(['supplier_1', 'supplier_2'], $supplierDirectories);
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
}

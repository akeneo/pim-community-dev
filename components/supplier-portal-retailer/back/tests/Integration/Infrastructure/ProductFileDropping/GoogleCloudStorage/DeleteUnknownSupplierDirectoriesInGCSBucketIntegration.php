<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\DeleteUnknownSupplierDirectoriesInGCSBucket;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DeleteUnknownSupplierDirectoriesInGCSBucketIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_1')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_2')
                ->build(),
        );
    }

    /** @test */
    public function itDeletesUnknownSupplierDirectoriesInGCSBucket(): void
    {
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
}

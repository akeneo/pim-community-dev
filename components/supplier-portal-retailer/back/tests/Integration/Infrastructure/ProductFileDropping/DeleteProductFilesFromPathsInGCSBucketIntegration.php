<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\DeleteProductFilesFromPaths;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;

final class DeleteProductFilesFromPathsInGCSBucketIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDeletesProductFilesFromPathsInGCSBucket(): void
    {
        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->write('supplier1/file1.xlsx', 'foo');
        $filesystem->write('supplier1/file2.xlsx', 'foo');
        $filesystem->write('supplier1/file3.xlsx', 'foo');
        $filesystem->write('supplier1/file4.xlsx', 'foo');

        ($this->get(DeleteProductFilesFromPaths::class))(['supplier1/file1.xlsx', 'supplier1/file2.xlsx']);

        $productFiles = $filesystem->listContents('supplier1')->toArray();
        $this->assertSame(
            'supplier1/file3.xlsx',
            $productFiles[0]->path(),
        );
        $this->assertSame(
            'supplier1/file4.xlsx',
            $productFiles[1]->path(),
        );
    }
}

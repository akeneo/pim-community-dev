<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DownloadStoredProductFileFromGCSBucketIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDownloadsAProductsFileFromAGCSBucket(): void
    {
        $fileContent = 'file content';

        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->write('path/to/file.xlsx', $fileContent);

        $streamedResource = ($this->get(DownloadStoredProductFile::class))('path/to/file.xlsx');

        $this->assertSame($fileContent, stream_get_contents($streamedResource));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;

final class DownloadStoredProductFileInGCSBucketIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDownloadsAProductsFileInAGCSBucket(): void
    {
        $fileContent = 'file content';

        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->write('path/to/file.xlsx', $fileContent);

        $streamedResource = ($this->get(DownloadStoredProductFile::class))(Path::fromString('path/to/file.xlsx'));

        $this->assertSame($fileContent, stream_get_contents($streamedResource));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheRequestedFileDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);
        ($this->get(DownloadStoredProductFile::class))(Path::fromString('path/to/unknown-file.xlsx'));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\StreamStoredProductFileFromGCSBucket;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class StreamStoredProductFileFromGCSBucketTest extends TestCase
{
    /** @test */
    public function itThrowsAProductFileDoesNotExistExceptionWhenTheFileDoesNotExist(): void
    {
        $filesystemProvider = $this->createMock(FilesystemProvider::class);
        $logger = new TestLogger();
        $sut = new StreamStoredProductFileFromGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)('path/to/file.xlsx');
        } catch (ProductFileDoesNotExist) {
            static::assertTrue($logger->hasError([
                'message' => 'Product file does not exist.',
                'context' => [
                    'data' => [
                        'path' => 'path/to/file.xlsx',
                    ],
                ],
            ]));
        }
    }

    /** @test */
    public function itThrowsAnUnableToReadProductFileExceptionWhenGCSIsUnableToCheckFileExistenceBecauseTheServiceIsUnavailable(): void
    {
        $filesystemProvider = $this->createMock(FilesystemProvider::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystemProvider
            ->expects($this->once())
            ->method('getFilesystem')
            ->with(Storage::FILE_STORAGE_ALIAS)
            ->willReturn($filesystem)
        ;
        $filesystem
            ->method('fileExists')
            ->willThrowException(new UnableToCheckFileExistence('Service unavailable.'))
        ;
        $logger = new TestLogger();
        $sut = new StreamStoredProductFileFromGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)('path/to/file.xlsx');
        } catch (UnableToReadProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'Product file could not be downloaded.',
                'context' => [
                    'data' => [
                        'path' => 'path/to/file.xlsx',
                        'error' => 'Service unavailable.',
                    ],
                ],
            ]));
        }
    }

    /** @test */
    public function itThrowsAnUnableToReadProductFileExceptionWhenGCSIsUnableToReadFileBecauseTheServiceIsUnavailable(): void
    {
        $filesystemProvider = $this->createMock(FilesystemProvider::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystemProvider
            ->expects($this->once())
            ->method('getFilesystem')
            ->with(Storage::FILE_STORAGE_ALIAS)
            ->willReturn($filesystem)
        ;
        $filesystem
            ->method('fileExists')
            ->willReturn(true)
        ;
        $filesystem
            ->method('readStream')
            ->willThrowException(new UnableToReadFile('Service unavailable.'))
        ;
        $logger = new TestLogger();
        $sut = new StreamStoredProductFileFromGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)('path/to/file.xlsx');
        } catch (UnableToReadProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'Product file could not be downloaded.',
                'context' => [
                    'data' => [
                        'path' => 'path/to/file.xlsx',
                        'error' => 'Service unavailable.',
                    ],
                ],
            ]));
        }
    }
}

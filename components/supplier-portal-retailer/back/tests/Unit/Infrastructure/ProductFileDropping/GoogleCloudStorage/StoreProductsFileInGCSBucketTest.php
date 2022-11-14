<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\StoreProductsFileInGCSBucket;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class StoreProductsFileInGCSBucketTest extends TestCase
{
    /** @test */
    public function itThrowsAnUnableToStoreProductFileExceptionWhenTheDirectoryCreationFailBecauseGCSServiceIsNotAvailable(): void
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
            ->method('createDirectory')
            ->willThrowException(new UnableToCreateDirectory('Service unavailable.'))
        ;
        $logger = new TestLogger();
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)(
                Code::fromString('supplier_code'),
                Filename::fromString('file.xlsx'),
                Identifier::fromString('d4d6d67d-528e-413d-805f-1b37fa5595bb'),
                '/tmp',
            );
        } catch (UnableToStoreProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'Product file could not be stored.',
                'context' => [
                    'data' => [
                        'fileIdentifier' => 'd4d6d67d-528e-413d-805f-1b37fa5595bb',
                        'filename' => 'file.xlsx',
                        'path' => 'supplier_code/d4d6d67d-528e-413d-805f-1b37fa5595bb-file.xlsx',
                        'error' => 'Service unavailable.',
                    ],
                ],
            ]));
        }
    }

    /** @test */
    public function itThrowsAnUnableToStoreProductFileExceptionWhenTheWriteInBucketFailBecauseGCSServiceIsNotAvailable(): void
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
            ->method('writeStream')
            ->willThrowException(new UnableToWriteFile('Service unavailable.'))
        ;
        $logger = new TestLogger();
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)(
                Code::fromString('supplier_code'),
                Filename::fromString('file.xlsx'),
                Identifier::fromString('d4d6d67d-528e-413d-805f-1b37fa5595bb'),
                '/tmp',
            );
        } catch (UnableToStoreProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'Product file could not be stored.',
                'context' => [
                    'data' => [
                        'fileIdentifier' => 'd4d6d67d-528e-413d-805f-1b37fa5595bb',
                        'filename' => 'file.xlsx',
                        'path' => 'supplier_code/d4d6d67d-528e-413d-805f-1b37fa5595bb-file.xlsx',
                        'error' => 'Service unavailable.',
                    ],
                ],
            ]));
        }
    }

    /** @test */
    public function itThrowsAnUnableToStoreProductFileExceptionWhenTheTemporaryPathIsNotReadable(): void
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
            ->method('writeStream')
            ->willThrowException(new UnableToWriteFile('Service unavailable.'))
        ;
        $logger = new TestLogger();
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider, $logger);
        try {
            ($sut)(
                Code::fromString('supplier_code'),
                Filename::fromString('file.xlsx'),
                Identifier::fromString('d4d6d67d-528e-413d-805f-1b37fa5595bb'),
                '/not-readable-path',
            );
        } catch (UnableToStoreProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'Temporary path is not readable.',
                'context' => [
                    'data' => [
                        'fileIdentifier' => 'd4d6d67d-528e-413d-805f-1b37fa5595bb',
                        'filename' => 'file.xlsx',
                        'path' => 'supplier_code/d4d6d67d-528e-413d-805f-1b37fa5595bb-file.xlsx',
                    ],
                ],
            ]));
        }
    }
}

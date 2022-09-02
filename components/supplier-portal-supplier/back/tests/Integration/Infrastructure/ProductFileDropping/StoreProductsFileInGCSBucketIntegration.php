<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\StoreProductsFileInGCSBucket;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StoreProductsFileInGCSBucketIntegration extends KernelTestCase
{
    /** @test */
    public function itStoresAProductsFileInAGCSBucketAndReturnsThePath(): void
    {
        file_put_contents('/tmp/products.xlsx', 'content');
        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider);

        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $fileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $expectedContents = [
            [
                'type' => 'file',
                'path' => sprintf('%s/%s-%s', 'supplier_a', $fileIdentifier, 'products.xlsx'),
            ],
        ];

        $path = ($sut)(
            Code::fromString('supplier_a'),
            Filename::fromString('products.xlsx'),
            $fileIdentifier,
            '/tmp/products.xlsx'
        );

        $customerFiles = $filesystem->listContents('supplier_a');

        $actualContents = [];
        foreach ($customerFiles as $object) {
            $actualContents[] = [
                'type' => $object['type'],
                'path' => $object['path'],
            ];
        }

        static::assertSame(sprintf('%s/%s-%s', 'supplier_a', $fileIdentifier, 'products.xlsx'), $path);
        static::assertSame($expectedContents, $actualContents);
    }

    /** @test */
    public function itThrowsAnExceptionIfFileIsNotReadable(): void
    {
        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider);

        $fileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');

        static::expectException(\RuntimeException::class);
        ($sut)(
            Code::fromString('supplier_a'),
            Filename::fromString('products.xlsx'),
            $fileIdentifier,
            '/tmp/products.xlsx'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $filesystem->deleteDirectory('supplier_a');
        if (file_exists('/tmp/products.xlsx')) {
            unlink('/tmp/products.xlsx');
        }
    }
}

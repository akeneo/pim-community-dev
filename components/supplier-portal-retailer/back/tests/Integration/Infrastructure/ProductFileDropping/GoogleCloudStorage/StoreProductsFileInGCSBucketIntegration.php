<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class StoreProductsFileInGCSBucketIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itStoresAProductsFileInAGCSBucketAndReturnsThePath(): void
    {
        file_put_contents('/tmp/products.xlsx', 'content');
        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $sut = $this->get(StoreProductsFile::class);

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
        $sut = $this->get(StoreProductsFile::class);

        $fileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');

        static::expectException(UnableToStoreProductFile::class);
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

<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\StoreProductsFileInGCSBucket;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StoreProductsFileInGCSBucketIntegration extends KernelTestCase
{
    /** @test */
    public function itStoresAProductsFileInAGCSBucket(): void
    {
        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider, 'customer_database_name');

        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $expectedContents = [
            ['type' => 'dir', 'path' => 'customer_database_name/supplier-a'],
            ['type' => 'file', 'path' => 'customer_database_name/supplier-a/products.xlsx'],
        ];

        ($sut)('supplier-a', Filename::fromString('products.xlsx'), 'content');

        $customerFiles = $filesystem->listContents('customer_database_name');

        $actualContents = [];
        foreach ($customerFiles as $object) {
            $actualContents[] = [
                'type' => $object['type'],
                'path' => $object['path'],
            ];
        }

        $supplierCustomerFiles = $filesystem->listContents('customer_database_name/supplier-a');
        foreach ($supplierCustomerFiles as $object) {
            $actualContents[] = [
                'type' => $object['type'],
                'path' => $object['path'],
            ];
        }

        static::assertSame($expectedContents, $actualContents);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $filesystem->deleteDirectory('customer_database_name');
    }
}

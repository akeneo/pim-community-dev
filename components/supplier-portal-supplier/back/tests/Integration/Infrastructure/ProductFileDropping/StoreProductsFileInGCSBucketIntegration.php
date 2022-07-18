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
        $sut = new StoreProductsFileInGCSBucket($filesystemProvider, 'pim_ucs_tenant_1');

        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $expectedContents = [
            ['type' => 'dir', 'path' => 'pim_ucs_tenant_1/supplier-a'],
            ['type' => 'file', 'path' => 'pim_ucs_tenant_1/supplier-a/products.xlsx'],
        ];

        ($sut)('supplier-a', Filename::fromString('products.xlsx'), 'content');

        $customerFiles = $filesystem->listContents('pim_ucs_tenant_1');

        $actualContents = [];
        foreach ($customerFiles as $object) {
            $actualContents[] = [
                'type' => $object['type'],
                'path' => $object['path'],
            ];
        }

        $supplierCustomerFiles = $filesystem->listContents('pim_ucs_tenant_1/supplier-a');
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

        $filesystem->deleteDirectory('pim_ucs_tenant_1');
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Spout\SpoutRemoteXlsxFileReaderFactory;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use OpenSpout\Reader\XLSX\Reader;

final class RemoteXlsxFileReaderFactoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAExcelReaderFromARemoteFile(): void
    {
        $sampleExcelFilePath = sprintf(
            '%s/components/supplier-portal-retailer/back/tests/Integration/files/suppliers_import.xlsx',
            static::$kernel->getProjectDir(),
        );

        $filesystemProvider = static::getContainer()->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->write('supplier1/file1.xlsx', file_get_contents($sampleExcelFilePath));

        $reader = $this->get(SpoutRemoteXlsxFileReaderFactory::class)->create('supplier1/file1.xlsx', 'file1.xlsx');

        $this->assertInstanceOf(Reader::class, $reader);
    }
}

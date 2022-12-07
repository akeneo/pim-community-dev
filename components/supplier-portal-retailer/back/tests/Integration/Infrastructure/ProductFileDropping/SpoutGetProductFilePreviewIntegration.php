<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePreview;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class SpoutGetProductFilePreviewIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itBuildsAPreviewOfAProductFileWithOnlyTheFirst20RowsAnd100ColumnsMax(): void
    {
        $this->copySampleExcelFileToGCS('product_files_1000_with_2_sheets.xlsx');

        $productFilePreview = $this->get(GetProductFilePreview::class)('product_files/product_files_1000_with_2_sheets.xlsx', 'product_files_1000_with_2_sheets.xlsx');

        $this->assertCount(20, $productFilePreview->preview);
        $this->assertCount(100, $productFilePreview->preview[1]);

        $this->assertSame('sku', $productFilePreview->preview[1][0]);
        $this->assertSame('Column_100', $productFilePreview->preview[1][99]);

        $this->assertSame('davos-0019', $productFilePreview->preview[20][0]);
        $this->assertSame('dummy', $productFilePreview->preview[20][99]);
    }

    /** @test */
    public function itBuildsAnEmptyPreviewOfAnEmptyProductFile(): void
    {
        $this->copySampleExcelFileToGCS('empty_excel_file.xlsx');

        $productFilePreview = $this->get(GetProductFilePreview::class)('product_files/empty_excel_file.xlsx', 'empty_excel_file.xlsx');

        $this->assertEmpty($productFilePreview->preview);
    }

    /** @test */
    public function itThrowsAnErrorIfTheProductFileCannotBeRead(): void
    {
        $this->copySampleExcelFileToGCS('invalid_excel_file.xlsx');

        $this->expectException(UnableToReadProductFile::class);
        $this->get(GetProductFilePreview::class)('product_files/invalid_excel_file.xlsx', 'invalid_excel_file.xlsx');
    }

    private function copySampleExcelFileToGCS(string $filename): void
    {
        $sampleExcelFilePath = sprintf(
            "%s/components/supplier-portal-retailer/back/tests/Integration/files/$filename",
            static::$kernel->getProjectDir(),
        );

        $filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
        $filesystem = $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $filesystem->write("product_files/$filename", file_get_contents($sampleExcelFilePath));
    }
}

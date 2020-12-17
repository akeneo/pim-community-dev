<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\AssetManager\Integration\Export;

use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class QuickExportProductAndProductModelWithAssetFilesIntegration extends AbstractExportWithAssetTestCase
{
    private const CSV_JOB_CODE = 'csv_product_quick_export';
    private AbstractItemMediaWriter $actualWriter;

    public function getWriter(): AbstractItemMediaWriter
    {
        return $this->actualWriter;
    }

    public function getWorkingPath(): string
    {
        return sys_get_temp_dir();
    }

    /** @test */
    public function it_quick_exports_products_and_product_models_in_csv_with_asset_media_files(): void
    {
        $this->loadProductsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_FILE_AS_MAIN_MEDA);
        $this->loadProductModelsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_FILE_AS_MAIN_MEDA);
        $config = [
            'locale' => 'en_US',
            'scope' => 'tablet',
            'ui_locale' => 'fr_FR',
            'filters' => [],
            'with_media' => true,
            'with_label' => false,
            'withHeader' => true,
            'filePathProduct' => '/tmp/export_products.csv',
            'filePathProductModel' => '/tmp/export_product_models.csv',
        ];
        $this->launchCsvExport(self::CSV_JOB_CODE, 'admin', $config);

        // Check product export file
        $productCsv = $this->getResultsFromExportedFile('/tmp/export_products.csv');
        $this->actualWriter = $this->get('pim_connector.writer.file.csv_product_quick_export');
        self::assertArrayHasKey(0, $productCsv);
        self::assertSame('asset1,asset2', $productCsv[0]['asset_attribute'] ?? null);
        self::assertFilePaths($productCsv[0]['asset_attribute-file_path'] ?? '', [
            'files/product_1/asset_attribute/file1.gif',
            'files/product_1/asset_attribute/file2.gif'
        ]);

        self::assertFileExistsInWorkingPath('files/product_1/asset_attribute/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_1/asset_attribute/file2.gif');

        self::assertArrayHasKey(1, $productCsv);
        self::assertSame('asset1,asset2', $productCsv[1]['localizable_asset_attribute-en_US'] ?? null);
        self::assertFilePaths($productCsv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'files/product_2/localizable_asset_attribute/en_US/file1.gif',
            'files/product_2/localizable_asset_attribute/en_US/file2.gif'
        ]);
        self::assertFileExistsInWorkingPath('files/product_2/localizable_asset_attribute/en_US/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertSame('asset2', $productCsv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        self::assertFilePaths($productCsv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'files/product_2/localizable_asset_attribute/fr_FR/file2.gif'
        ]);
        self::assertFileExistsInWorkingPath('files/product_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertArrayHasKey('localizable_asset_attribute-de_DE', $productCsv[1]);

        // Check product model export file
        $productModelCsv = $this->getResultsFromExportedFile('/tmp/export_product_models.csv');
        $this->actualWriter = $this->get('pim_connector.writer.file.csv_product_model_quick_export');
        self::assertArrayHasKey(0, $productModelCsv);
        self::assertSame('asset1,asset2', $productModelCsv[0]['asset_attribute'] ?? null);
        $this->assertFilePaths($productModelCsv[0]['asset_attribute-file_path'] ?? '', [
            'files/product_model_1/asset_attribute/file1.gif',
            'files/product_model_1/asset_attribute/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_1/asset_attribute/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_model_1/asset_attribute/file2.gif');

        self::assertArrayHasKey(1, $productModelCsv);
        self::assertSame('asset1,asset2', $productModelCsv[1]['localizable_asset_attribute-en_US'] ?? null);
        $this->assertFilePaths($productModelCsv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'files/product_model_2/localizable_asset_attribute/en_US/file1.gif',
            'files/product_model_2/localizable_asset_attribute/en_US/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/en_US/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertSame('asset2', $productModelCsv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        $this->assertFilePaths($productModelCsv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertArrayHasKey('localizable_asset_attribute-de_DE', $productModelCsv[1]);
    }

    /** @test */
    public function it_quick_exports_products_and_product_models_in_csv_with_asset_media_links(): void
    {
        $this->loadProductsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA);
        $this->loadProductModelsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA);
        $config = [
            'locale' => 'en_US',
            'scope' => 'tablet',
            'ui_locale' => 'fr_FR',
            'filters' => [],
            'with_media' => true,
            'with_label' => false,
            'withHeader' => true,
            'filePathProduct' => '/tmp/export_products.csv',
            'filePathProductModel' => '/tmp/export_product_models.csv',
        ];
        $this->launchCsvExport(self::CSV_JOB_CODE, 'admin', $config);

        // Check product export file
        $productCsv = $this->getResultsFromExportedFile('/tmp/export_products.csv');
        self::assertArrayHasKey(0, $productCsv);
        self::assertSame('asset1,asset2', $productCsv[0]['asset_attribute'] ?? null);
        self::assertFilePaths($productCsv[0]['asset_attribute-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);
        self::assertArrayHasKey(1, $productCsv);
        self::assertSame('asset1,asset2', $productCsv[1]['localizable_asset_attribute-en_US'] ?? null);
        self::assertFilePaths($productCsv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);
        self::assertSame('asset2', $productCsv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        self::assertFilePaths($productCsv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'http://www.example.com/link2',
        ]);
        self::assertArrayHasKey('localizable_asset_attribute-de_DE', $productCsv[1]);

        // Check product model export file
        $productModelCsv = $this->getResultsFromExportedFile('/tmp/export_product_models.csv');
        self::assertArrayHasKey(0, $productModelCsv);
        self::assertSame('asset1,asset2', $productModelCsv[0]['asset_attribute'] ?? null);
        self::assertSame(
            'http://www.example.com/link1,http://www.example.com/link2',
            $productModelCsv[0]['asset_attribute-file_path'] ?? null
        );
        $this->assertFilePaths($productModelCsv[0]['asset_attribute-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);

        self::assertArrayHasKey(1, $productModelCsv);
        self::assertSame('asset1,asset2', $productModelCsv[1]['localizable_asset_attribute-en_US'] ?? null);
        $this->assertFilePaths($productModelCsv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);
        self::assertSame('asset2', $productModelCsv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        $this->assertFilePaths($productModelCsv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'http://www.example.com/link2',
        ]);
        self::assertArrayHasKey('localizable_asset_attribute-de_DE', $productModelCsv[1]);
    }
}

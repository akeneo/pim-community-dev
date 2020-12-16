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
final class ExportProductModelWithAssetFilesIntegration extends AbstractExportWithAssetTestCase
{
    private const CSV_JOB_CODE = 'csv_product_model_export';

    protected function getWriter(): AbstractItemMediaWriter
    {
        return $this->get('pim_connector.writer.file.csv_product_model');
    }

    /** @test */
    public function it_exports_product_models_in_csv_with_asset_media_files(): void
    {
        $this->loadProductModelsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_FILE_AS_MAIN_MEDA);
        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR'],
                ],
            ],
            'with_media' => true,
        ];
        $csv = $this->launchCsvExportAndReturnArrayResults(self::CSV_JOB_CODE, 'admin', $config);

        self::assertArrayHasKey(0, $csv);
        self::assertSame('asset1,asset2', $csv[0]['asset_attribute'] ?? null);
        $this->assertFilePaths($csv[0]['asset_attribute-file_path'] ?? '', [
            'files/product_model_1/asset_attribute/file1.gif',
            'files/product_model_1/asset_attribute/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_1/asset_attribute/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_model_1/asset_attribute/file2.gif');

        self::assertArrayHasKey(1, $csv);
        self::assertSame('asset1,asset2', $csv[1]['localizable_asset_attribute-en_US'] ?? null);
        $this->assertFilePaths($csv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'files/product_model_2/localizable_asset_attribute/en_US/file1.gif',
            'files/product_model_2/localizable_asset_attribute/en_US/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/en_US/file1.gif');
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertSame('asset2', $csv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        $this->assertFilePaths($csv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif',
        ]);
        self::assertFileExistsInWorkingPath('files/product_model_2/localizable_asset_attribute/fr_FR/file2.gif');
        self::assertArrayNotHasKey('localizable_asset_attribute-de_DE', $csv[1]);
    }

    /** @test */
    public function it_exports_product_models_in_csv_with_asset_media_links(): void
    {
        $this->loadProductModelsWithAssetFamilyReferenceData(self::ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA);
        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR'],
                ],
            ],
            'with_media' => true,
        ];
        $csv = $this->launchCsvExportAndReturnArrayResults(self::CSV_JOB_CODE, 'admin', $config);

        self::assertArrayHasKey(0, $csv);
        self::assertSame('asset1,asset2', $csv[0]['asset_attribute'] ?? null);
        self::assertSame(
            'http://www.example.com/link1,http://www.example.com/link2',
            $csv[0]['asset_attribute-file_path'] ?? null
        );
        $this->assertFilePaths($csv[0]['asset_attribute-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);

        self::assertArrayHasKey(1, $csv);
        self::assertSame('asset1,asset2', $csv[1]['localizable_asset_attribute-en_US'] ?? null);
        $this->assertFilePaths($csv[1]['localizable_asset_attribute-en_US-file_path'] ?? '', [
            'http://www.example.com/link1',
            'http://www.example.com/link2',
        ]);
        self::assertSame('asset2', $csv[1]['localizable_asset_attribute-fr_FR'] ?? null);
        $this->assertFilePaths($csv[1]['localizable_asset_attribute-fr_FR-file_path'] ?? '', [
            'http://www.example.com/link2',
        ]);
        self::assertArrayNotHasKey('localizable_asset_attribute-de_DE', $csv[1]);
    }
}

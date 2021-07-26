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

namespace Akeneo\AssetManager\Integration\PublicApi\Enrich;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\SqlGetMainMediaFileInfoCollection;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetMainMediaFileInfoTest extends SqlIntegrationTestCase
{
    private SqlGetMainMediaFileInfoCollection $sqlGetMainMediaFileInfo;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlGetMainMediaFileInfo = $this->get('akeneo_assetmanager.infrastructure.persistence.query.enrich.get_main_media_file_info_collection_public_api');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadDataset();
    }

    /** @test */
    public function it_returns_a_list_of_main_media_values(): void
    {
        $results = $this->sqlGetMainMediaFileInfo->forAssetFamilyAndAssetCodes(
            'asset_family_packshot',
            ['asset_family_packshot_asset_1', 'asset_family_packshot_asset_2'],
            null,
            'fr_FR'
        );

        $expectedFileInfo1 = new FileInfo();
        $expectedFileInfo1->setKey('test/main_image_asset_1_fr_FR.jpg');
        $expectedFileInfo1->setOriginalFilename('main_image_asset_1_fr_FR.jpg');

        $expectedFileInfo2 = new FileInfo();
        $expectedFileInfo2->setKey('test/main_image_asset_2_fr_FR.jpg');
        $expectedFileInfo2->setOriginalFilename('main_image_asset_2_fr_FR.jpg');

        self::assertEqualsCanonicalizing([
            $expectedFileInfo1,
            $expectedFileInfo2
        ], $results);
    }

    /** @test */
    public function it_returns_an_empty_array_for_unknown_asset_family_or_asset_codes(): void
    {
        $results = $this->sqlGetMainMediaFileInfo->forAssetFamilyAndAssetCodes(
            'asset_family_1',
            ['asset_2_a'],
            null,
            null
        );
        self::assertEmpty($results);

        $results = $this->sqlGetMainMediaFileInfo->forAssetFamilyAndAssetCodes(
            'unknown',
            ['asset_1_a', 'asset_2_a'],
            null,
            null
        );
        self::assertEmpty($results);
    }

    private function loadDataset(): void
    {
        $assetFamilyPackshot = $this->createAssetFamily('asset_family_packshot');
        $assetPackshot = $this->createAsset($assetFamilyPackshot, '1');
        $assetPackshot = $this->createAsset($assetFamilyPackshot, '2');

        $assetFamilyAtmosphere = $this->createAssetFamily('asset_family_atmosphere');
        $assetAtmosphere = $this->createAsset($assetFamilyAtmosphere, '1');
        $assetAtmosphere = $this->createAsset($assetFamilyAtmosphere, '2');
    }

    private function createAssetFamily(string $assetFamilyIdentifier): AssetFamily
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetFamilyRepository->create(AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty())
        );

        return $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
    }

    private function createAsset(AssetFamily $assetFamily, string $assetCode): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString(sprintf('%s_asset_%s', $assetFamily->getIdentifier(), $assetCode)),
                $assetFamily->getIdentifier(),
                AssetCode::fromString(sprintf('%s_asset_%s', $assetFamily->getIdentifier(), $assetCode)),
                ValueCollection::fromValues([
                    Value::create(
                        $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('en_US'),
                        FileData::createFromNormalize([
                            'filePath' => sprintf('test/main_image_asset_%s_en_US.jpg', $assetCode),
                            'originalFilename' => sprintf('main_image_asset_%s_en_US.jpg', $assetCode),
                            'size' => 100,
                            'mimeType' => 'image/jpg',
                            'extension' => '.jpg',
                            'updatedAt' => '2021-01-22T15:16:21+0000',
                        ])
                    ),
                    Value::create(
                        $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('fr_FR'),
                        FileData::createFromNormalize([
                            'filePath' => sprintf('test/main_image_asset_%s_fr_FR.jpg', $assetCode),
                            'originalFilename' => sprintf('main_image_asset_%s_fr_FR.jpg', $assetCode),
                            'size' => 100,
                            'mimeType' => 'image/jpg',
                            'extension' => '.jpg',
                            'updatedAt' => '2021-01-22T15:16:21+0000',
                        ])
                    ),
                ])
            )
        );
    }
}

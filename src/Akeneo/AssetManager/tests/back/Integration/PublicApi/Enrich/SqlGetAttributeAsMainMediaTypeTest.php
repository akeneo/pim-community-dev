<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
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
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\SqlGetAttributeAsMainMediaType;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

final class SqlGetAttributeAsMainMediaTypeTest extends SqlIntegrationTestCase
{
    private SqlGetAttributeAsMainMediaType $sqlGetAttributeAsMainMediaType;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlGetAttributeAsMainMediaType = $this->get('akeneo_assetmanager.infrastructure.persistence.query.enrich.get_attribute_as_main_media_type_public_api');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadDataset();
    }

    /** @test */
    public function it_says_if_an_asset_family_main_media_attribute_is_a_media_file(): void
    {
        $isMediaFile = $this->sqlGetAttributeAsMainMediaType->isMediaFile('asset_family_packshot');
        self::assertTrue($isMediaFile);
    }

    /** @test */
    public function it_returns_false_for_unknown_asset_family(): void
    {
        $isMediaFile = $this->sqlGetAttributeAsMainMediaType->isMediaLink('unknown');
        self::assertFalse($isMediaFile);

        $isMediaLink = $this->sqlGetAttributeAsMainMediaType->isMediaLink('unknown');
        self::assertFalse($isMediaLink);
    }

    private function loadDataset(): void
    {
        $assetFamilyPackshot = $this->createAssetFamily('asset_family_packshot');
        $this->createAsset($assetFamilyPackshot, '1');
        $this->createAsset($assetFamilyPackshot, '2');

        $assetFamilyAtmosphere = $this->createAssetFamily('asset_family_atmosphere');
        $this->createAsset($assetFamilyAtmosphere, '1');
        $this->createAsset($assetFamilyAtmosphere, '2');
    }

    private function createAssetFamily(string $assetFamilyIdentifier): AssetFamily
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetFamilyRepository->create(
            AssetFamily::create(
                $assetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
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

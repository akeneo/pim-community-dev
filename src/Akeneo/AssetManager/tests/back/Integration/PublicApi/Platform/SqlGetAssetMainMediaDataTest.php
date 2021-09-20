<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\PublicApi\Platform;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAssetMainMediaData;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlGetAssetMainMediaDataTest extends SqlIntegrationTestCase
{
    private SqlGetAssetMainMediaData $sqlGetAssetMainMediaData;
    private AssetFamilyRepositoryInterface $assetFamilyRepository;
    private AssetRepositoryInterface $assetRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAssetMainMediaData = $this->get('akeneo_assetmanager.infrastructure.persistence.query.platform.get_asset_main_media_data_public_api');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadDataset();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_for_unknown_asset_family_or_asset_codes() {
        $mainMediaDataForUnknownFamily = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes('unknown', ['simple_packshot_asset_1', 'simple_packshot_asset_2'], null, null);
        self::assertEmpty($mainMediaDataForUnknownFamily);
        $mainMediaDataForUnknownAssetCodes = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes('simple_packshot', ['unknown'], null, null);
        self:self::assertEmpty($mainMediaDataForUnknownAssetCodes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_any_asset_exist_for_channel_or_locale() {
        $mainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes('simple_packshot', ['simple_packshot_asset_1'], 'ecommerce', null);
        self:self::assertEmpty($mainMediaData);
    }

    /**
     * @test
     */
    public function it_returns_asset_main_media_data_for_family_with_a_media_file_as_main_media() {
        $mainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'simple_packshot',
            ['simple_packshot_asset_1', 'simple_packshot_asset_2'],
            null,
            null
        );
        self::assertEqualsCanonicalizing([
            'simple_packshot_asset_1' => [
                'filePath' => 'test/asset_1_na_na.jpg',
                'fileKey' => 'test/asset_1_na_na.jpg',
                'originalFilename' => 'asset_1_na_na.jpg',
            ],
            'simple_packshot_asset_2' => [
                'filePath' => 'test/asset_2_na_na.jpg',
                'fileKey' => 'test/asset_2_na_na.jpg',
                'originalFilename' => 'asset_2_na_na.jpg',
            ]
        ], $mainMediaData);

        $scopedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'scoped_packshot',
            ['scoped_packshot_asset_1', 'scoped_packshot_asset_2'],
            'print',
            null
        );
        self::assertEqualsCanonicalizing([
            'scoped_packshot_asset_1' => [
                'filePath' => 'test/asset_1_print_na.jpg',
                'fileKey' => 'test/asset_1_print_na.jpg',
                'originalFilename' => 'asset_1_print_na.jpg',
            ],
            'scoped_packshot_asset_2' => [
                'filePath' => 'test/asset_2_print_na.jpg',
                'fileKey' => 'test/asset_2_print_na.jpg',
                'originalFilename' => 'asset_2_print_na.jpg',
            ]
        ], $scopedMainMediaData);

        $localizedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'localized_packshot',
            ['localized_packshot_asset_1', 'localized_packshot_asset_2'],
            null,
            'fr_FR'
        );
        self::assertEqualsCanonicalizing([
            'localized_packshot_asset_1' => [
                'filePath' => 'test/asset_1_na_fr_FR.jpg',
                'fileKey' => 'test/asset_1_na_fr_FR.jpg',
                'originalFilename' => 'asset_1_na_fr_FR.jpg',
            ],
            'localized_packshot_asset_2' => [
                'filePath' => 'test/asset_2_na_fr_FR.jpg',
                'fileKey' => 'test/asset_2_na_fr_FR.jpg',
                'originalFilename' => 'asset_2_na_fr_FR.jpg',
            ]
        ], $localizedMainMediaData);

        $scopedAndLocalizedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'scoped_and_localized_packshot',
            ['scoped_and_localized_packshot_asset_1', 'scoped_and_localized_packshot_asset_2'],
            'ecommerce',
            'fr_FR'
        );
        self::assertEqualsCanonicalizing([
            'scoped_and_localized_packshot_asset_1' => [
                'filePath' => 'test/asset_1_ecommerce_fr_FR.jpg',
                'fileKey' => 'test/asset_1_ecommerce_fr_FR.jpg',
                'originalFilename' => 'asset_1_ecommerce_fr_FR.jpg',
            ],
            'scoped_and_localized_packshot_asset_2' => [
                'filePath' => 'test/asset_2_ecommerce_fr_FR.jpg',
                'fileKey' => 'test/asset_2_ecommerce_fr_FR.jpg',
                'originalFilename' => 'asset_2_ecommerce_fr_FR.jpg',
            ]
        ], $scopedAndLocalizedMainMediaData);
    }

    /**
     * @test
     */
    public function it_returns_asset_main_media_data_for_family_with_a_media_link_as_main_media() {
        $mainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'simple_notice',
            ['simple_notice_asset_1', 'simple_notice_asset_2'],
            null,
            null
        );
        self::assertEqualsCanonicalizing([
            'simple_notice_asset_1' => 'http://asset_1_na_na',
            'simple_notice_asset_2' => 'http://asset_2_na_na',
        ], $mainMediaData);

        $scopedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'scoped_notice',
            ['scoped_notice_asset_1', 'scoped_notice_asset_2'],
            'ecommerce',
            null
        );
        self::assertEqualsCanonicalizing([
            'scoped_notice_asset_1' => 'http://asset_1_ecommerce_na',
            'scoped_notice_asset_2' => 'http://asset_2_ecommerce_na',
        ], $scopedMainMediaData);

        $localizedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'localized_notice',
            ['localized_notice_asset_1', 'localized_notice_asset_2'],
            null,
            'fr_FR'
        );
        self::assertEqualsCanonicalizing([
            'localized_notice_asset_1' => 'http://asset_1_na_fr_FR',
            'localized_notice_asset_2' => 'http://asset_2_na_fr_FR',
        ], $localizedMainMediaData);

        $scopedAndLocalizedMainMediaData = $this->sqlGetAssetMainMediaData->forAssetFamilyAndAssetCodes(
            'scoped_and_localized_notice',
            ['scoped_and_localized_notice_asset_1', 'scoped_and_localized_notice_asset_2'],
            'ecommerce',
            'fr_FR'
        );
        self::assertEqualsCanonicalizing([
            'scoped_and_localized_notice_asset_1' => 'http://asset_1_ecommerce_fr_FR',
            'scoped_and_localized_notice_asset_2' => 'http://asset_2_ecommerce_fr_FR',
        ], $scopedAndLocalizedMainMediaData);
    }

    private function loadDataset() {
        $families = [];
        foreach (['packshot', 'notice'] as $baseFamilyName) {
            foreach (['simple', 'scoped', 'localized', 'scoped_and_localized'] as $variantFamilyName) {
                $familyName = sprintf('%s_%s', $variantFamilyName, $baseFamilyName);
                $isMediaLinkFamily = $baseFamilyName === 'notice';
                $families[$familyName] = $isMediaLinkFamily ?
                    $this->createAssetFamilyWithMediaLinkAsMainMediaAttribute($familyName)
                    :
                    $this->createAssetFamilyWithMediaFileAsMainMediaAttribute($familyName)
                ;

                switch ($variantFamilyName) {
                    case 'simple':
                        $assetValues = [
                            [
                                'channel' => null,
                                'locale' => null
                            ]
                        ];
                        break;
                    case 'scoped':
                        $assetValues = [
                            [
                                'channel' => 'ecommerce',
                                'locale' => null
                            ],
                            [
                                'channel' => 'print',
                                'locale' => null
                            ]
                        ];
                        break;
                    case 'localized':
                        $assetValues = [
                            [
                                'channel' => null,
                                'locale' => 'en_US'
                            ],
                            [
                                'channel' => null,
                                'locale' => 'fr_FR'
                            ]
                        ];
                        break;
                    case 'scoped_and_localized':
                        $assetValues = [
                            [
                                'channel' => 'ecommerce',
                                'locale' => 'en_US'
                            ],
                            [
                                'channel' => 'ecommerce',
                                'locale' => 'fr_FR'
                            ]
                        ];
                        break;
                    default:
                        $assetValues = [];
                        break;
                }

                for ($i = 1; $i < 3; $i++) {
                    $assetCode = sprintf('asset_%s', $i);
                    $this->createAsset($families[$familyName], $assetCode, array_map(
                        function ($value) use ($assetCode, $isMediaLinkFamily) {
                            $dataString = sprintf('%s_%s_%s', $assetCode, $value['channel'] ?? 'na', $value['locale'] ?? 'na');
                            $data = $isMediaLinkFamily ?
                                MediaLinkData::fromString(sprintf('http://%s', $dataString))
                                :
                                FileData::createFromNormalize([
                                    'filePath' => sprintf('test/%s.jpg', $dataString),
                                    'originalFilename' => sprintf('%s.jpg', $dataString),
                                    'size' => 100,
                                    'mimeType' => 'image/jpg',
                                    'extension' => '.jpg',
                                    'updatedAt' => '2021-01-22T15:16:21+0000',
                                ]);

                            return array_merge($value, ['data' => $data]);
                        },
                        $assetValues
                    ));
                }
            }
        }
    }

    private function createAssetFamilyWithMediaFileAsMainMediaAttribute(string $assetFamilyIdentifier): AssetFamily
    {
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        return $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
    }

    private function createAssetFamilyWithMediaLinkAsMainMediaAttribute(string $assetFamilyIdentifier): AssetFamily
    {
        $assetFamily = $this->createAssetFamilyWithMediaFileAsMainMediaAttribute($assetFamilyIdentifier);

        $mediaLinkIdentifier = AttributeIdentifier::fromString(sprintf('%s_url', $assetFamilyIdentifier));
        $mediaLinkAttribute = MediaLinkAttribute::create(
            $mediaLinkIdentifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('url'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString(MediaType::PDF)
        );

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');;
        $attributeRepository->create($mediaLinkAttribute);
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($mediaLinkIdentifier));
        $this->assetFamilyRepository->update($assetFamily);
        return $assetFamily;
    }

    private function createAsset(AssetFamily $assetFamily, string $assetCode, array $values) {
        $assetValues = array_map(function($value) use ($assetFamily) {
            return Value::create(
                $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                $value['channel'] ? ChannelReference::createFromNormalized($value['channel']) : ChannelReference::noReference(),
                $value['locale'] ? LocaleReference::createFromNormalized($value['locale']) : LocaleReference::noReference(),
                $value['data']
            );
        }, $values);

        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString(sprintf('%s_%s', $assetFamily->getIdentifier(), $assetCode)),
                $assetFamily->getIdentifier(),
                AssetCode::fromString(sprintf('%s_%s', $assetFamily->getIdentifier(), $assetCode)),
                ValueCollection::fromValues($assetValues)
            )
        );
    }
}

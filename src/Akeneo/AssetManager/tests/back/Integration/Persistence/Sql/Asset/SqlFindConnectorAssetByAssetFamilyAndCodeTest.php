<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetByAssetFamilyAndCodeInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAssetByAssetFamilyAndCodeTest extends SqlIntegrationTestCase
{
    private AssetRepositoryInterface $assetRepository;

    private FindConnectorAssetByAssetFamilyAndCodeInterface $findConnectorAssetQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->findConnectorAssetQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_asset_by_asset_family_and_code');
        $this->resetDB();
        $this->createAssetFamilyWithAttributesAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_asset()
    {
        $asset = $this->createStarckAsset();

        $expectedAsset = new ConnectorAsset(
            $asset->getCode(),
            [
                'name'  => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'Philippe Stark',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'Philippe Stark',
                    ]
                ],
                'main_image' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'test/image_1.jpg',
                    ]
                ],
                'favorite_color' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'black',
                    ]
                ],
                'materials' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => ['plastic', 'metal'],
                    ]
                ],
                'front_view' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'house_front_view'
                    ]
                ],
                'front_view_dam' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'house_front_view'
                    ]
                ],
            ],
            (new \DateTimeImmutable('@0'))
                ->setTimezone(new \DateTimeZone(date_default_timezone_get())),
            (new \DateTimeImmutable('@3600'))
                ->setTimezone(new \DateTimeZone(date_default_timezone_get())),
        );

        $assetFound = $this->findConnectorAssetQuery->find(AssetFamilyIdentifier::fromString('designer'), $asset->getCode());

        $this->assertSameAssets($expectedAsset, $assetFound);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_asset_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('Foo');

        $assetFound = $this->findConnectorAssetQuery->find($assetFamilyIdentifier, $assetCode);

        $this->assertNull($assetFound);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createStarckAsset(): Asset
    {
        $assetCode = AssetCode::fromString('starck');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg')
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    AttributeIdentifier::fromString('main_image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                ),
                Value::create(
                    AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    OptionData::createFromNormalize('black')
                ),
                Value::create(
                    AttributeIdentifier::fromString('materials_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    OptionCollectionData::createFromNormalize(['plastic', 'metal'])
                ),
                Value::Create(
                    AttributeIdentifier::fromString('front_view_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    MediaLinkData::fromString('house_front_view')
                ),
                Value::Create(
                    AttributeIdentifier::fromString('front_view_dam_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    MediaLinkData::fromString('house_front_view')
                )
            ]),
            new \DateTimeImmutable('@0'),
            new \DateTimeImmutable('@3600'),
        );

        $this->assetRepository->create($asset);

        return $asset;
    }

    private function createAssetFamilyWithAttributesAndAssets(): void
    {
        $repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        foreach (['designer', 'country', 'brand'] as $identifier) {
            $assetFamilyDesigner = AssetFamily::create(
                AssetFamilyIdentifier::fromString($identifier),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            );
            $repository->create($assetFamilyDesigner);
        }

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $image = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'main_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );

        $favoriteColor = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'favorite_color', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $favoriteColor->setOptions([
            AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('black'), LabelCollection::fromArray([])),
        ]);

        $materials = OptionCollectionAttribute::create(
            AttributeIdentifier::create('designer', 'materials', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('materials'),
            LabelCollection::fromArray(['en_US' => 'Materials']),
            AttributeOrder::fromInteger(7),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $materials->setOptions([
            AttributeOption::create(OptionCode::fromString('metal'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('plastic'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('wood'), LabelCollection::fromArray([])),
        ]);

        $frontView = MediaLinkAttribute::create(
            AttributeIdentifier::create('designer', 'front_view', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('front_view'),
            LabelCollection::fromArray(['en_US' => 'Front View']),
            AttributeOrder::fromInteger(8),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString(''),
            Suffix::fromString(''),
            MediaLinkMediaType::fromString(MediaLinkMediaType::IMAGE)
        );

        $frontViewDam = MediaLinkAttribute::create(
            AttributeIdentifier::create('designer', 'front_view_dam', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('front_view_dam'),
            LabelCollection::fromArray(['en_US' => 'Front View Dam']),
            AttributeOrder::fromInteger(9),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://my-dam.com/'),
            Suffix::fromString('/500x500/thumbnail'),
            MediaLinkMediaType::fromString(MediaLinkMediaType::IMAGE)
        );

        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
        $attributesRepository->create($favoriteColor);
        $attributesRepository->create($materials);
        $attributesRepository->create($frontView);
        $attributesRepository->create($frontViewDam);

        $countryAsset = Asset::create(
            AssetIdentifier::fromString('country_france_fingerprint'),
            AssetFamilyIdentifier::fromString('country'),
            AssetCode::fromString('france'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('France')
                ),
            ]),
            new \DateTimeImmutable('@0'),
            new \DateTimeImmutable('@3600'),
        );
        $this->assetRepository->create($countryAsset);

        foreach (['kartell', 'lexon', 'cogip'] as $code) {
            $brandAsset = Asset::create(
                AssetIdentifier::fromString(sprintf('brand_%s_fingerprint', $code)),
                AssetFamilyIdentifier::fromString('brand'),
                AssetCode::fromString($code),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(ucfirst($code))
                    ),
                ]),
                new \DateTimeImmutable('@0'),
                new \DateTimeImmutable('@3600'),
            );
            $this->assetRepository->create($brandAsset);
        }
    }

    private function assertSameAssets(ConnectorAsset $expectedAsset, ConnectorAsset $currentAsset): void
    {
        $expectedAsset = $expectedAsset->normalize();
        $expectedAsset['values'] = $this->sortAssetValues($expectedAsset['values']);

        $currentAsset = $currentAsset->normalize();
        $currentAsset['values'] = $this->sortAssetValues($currentAsset['values']);

        $this->assertEquals($expectedAsset, $currentAsset);
    }

    private function sortAssetValues(array $assetValues): array
    {
        ksort($assetValues);

        foreach ($assetValues as $attributeCode => $assetValue) {
            foreach ($assetValue as $key => $value) {
                if (is_array($value['data'])) {
                    sort($value['data']);
                    $assetValues[$attributeCode][$key] = $value['data'];
                }
            }
        }

        return $assetValues;
    }
}

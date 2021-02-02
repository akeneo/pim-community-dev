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

namespace Akeneo\AssetManager\Integration\PublicApi\Onboarder;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlAssetRepository;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\SqlAssetFamilyRepository;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\SqlAttributeRepository;
use Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Quentin Favrie <quentin.favrie@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class FindAssetsByIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var Onboarder\FindAssetsByIdentifiers */
    private $query;

    /** @var AssetIdentifier[] */
    private $identifiers = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.onboarder.find_assets_by_identifiers');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_all_asset()
    {
        $assets = $this->query->find([
            (string)$this->identifiers['absorb_atmosphere_1'],
            (string)$this->identifiers['notice_1'],
        ]);
        $assets = iterator_to_array($assets);
        Assert::assertEquals([
            new Onboarder\Asset(
                (string)$this->identifiers['absorb_atmosphere_1'],
                ['de_DE' => null, 'en_US' => null, 'fr_FR' => null],
                'absorb_atmosphere_1',
                'atmosphere',
                [
                    'atmosphere_image' => [
                        'data' => [
                            'size' => 5596,
                            'filePath' => 'absorb_atmosphere.jpg',
                            'mimeType' => 'image/jpeg',
                            'extension' => 'jpeg',
                            'updatedAt' => '2019-11-22T15:16:21+0000',
                            'originalFilename' => 'absorb_atmosphere.jpg',
                        ],
                        'locale' => null,
                        'channel' => null,
                        'attribute' => 'atmosphere_image',
                    ],
                ],
                'media_file',
                'image'
            ),
            new Onboarder\Asset(
                (string)$this->identifiers['notice_1'],
                ['de_DE' => null, 'en_US' => null, 'fr_FR' => null],
                'notice_1',
                'notices',
                [
                    'notice_link' => [
                        'data' => 'https://www.ikea.com/fr/fr/manuals/stockholm-buffet__AA-2180177-1_pub.pdf',
                        'locale' => null,
                        'channel' => null,
                        'attribute' => 'notice_link',
                    ],
                ],
                'media_link',
                'pdf'
            )
        ], $assets);
    }

    /**
     * @test
     */
    public function it_finds_no_asset_when_there_is_no_identifiers()
    {
        $assets = $this->query->find([]);
        $assets = iterator_to_array($assets);
        Assert::assertCount(0, $assets);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $this->createAssetFamily(
            AssetFamilyIdentifier::fromString('atmosphere'),
            ['en_US' => 'Atmosphere']
        );
        $this->createMediaFileAttributeAsMainMedia(
            AssetFamilyIdentifier::fromString('atmosphere'),
            'atmosphere_image',
            ['en_US' => 'Image'],
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $this->createAsset(
            AssetFamilyIdentifier::fromString('atmosphere'),
            'absorb_atmosphere_1',
            [
                [
                    'attribute' => 'atmosphere_image',
                    'channel' => null,
                    'locale' => null,
                    'data' => [
                        'size' => 5596,
                        'filePath' => 'absorb_atmosphere.jpg',
                        'mimeType' => 'image/jpeg',
                        'extension' => 'jpeg',
                        'originalFilename' => 'absorb_atmosphere.jpg',
                        'updatedAt' => '2019-11-22T15:16:21+0000',
                    ],
                    'type' => FileData::class,
                ],
                [
                    'attribute' => 'foo',
                    'channel' => null,
                    'locale' => null,
                    'data' => 'foo',
                    'type' => TextData::class,
                ],
            ]
        );

        $this->createAssetFamily(
            AssetFamilyIdentifier::fromString('packshot'),
            ['en_US' => 'Packshot']
        );
        $this->createMediaFileAttributeAsMainMedia(
            AssetFamilyIdentifier::fromString('packshot'),
            'packshot_image',
            ['en_US' => 'Packshot'],
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $this->createAsset(
            AssetFamilyIdentifier::fromString('packshot'),
            'packshot_1',
            [
                [
                    'attribute' => 'packshot_image',
                    'channel' => null,
                    'locale' => 'en_US',
                    'data' => [
                        'size' => 5596,
                        'filePath' => 'packshot-en_US.jpg',
                        'mimeType' => 'image/jpeg',
                        'extension' => 'jpeg',
                        'originalFilename' => 'packshot-en_US.jpg',
                        'updatedAt' => '2019-11-22T15:16:21+0000',
                    ],
                    'type' => FileData::class,
                ],
                [
                    'attribute' => 'packshot_image',
                    'channel' => null,
                    'locale' => 'fr_FR',
                    'data' => [
                        'size' => 5596,
                        'filePath' => 'packshot-fr_FR.jpg',
                        'mimeType' => 'image/jpeg',
                        'extension' => 'jpeg',
                        'originalFilename' => 'packshot-fr_FR.jpg',
                        'updatedAt' => '2019-11-22T15:16:21+0000',
                    ],
                    'type' => FileData::class,
                ],
            ]
        );

        $this->createAssetFamily(
            AssetFamilyIdentifier::fromString('notices'),
            ['en_US' => 'Notices']
        );
        $this->createMediaLinkAttributeAsMainMedia(
            AssetFamilyIdentifier::fromString('notices'),
            'notice_link',
            ['en_US' => 'Notices'],
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $this->createAsset(
            AssetFamilyIdentifier::fromString('notices'),
            'notice_1',
            [
                [
                    'attribute' => 'notice_link',
                    'channel' => null,
                    'locale' => null,
                    'data' => 'https://www.ikea.com/fr/fr/manuals/stockholm-buffet__AA-2180177-1_pub.pdf',
                    'type' => MediaLinkData::class,
                ],
            ]
        );
    }

    private function createAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $labels
    ) {
        /** @var SqlAssetFamilyRepository $assetFamilyRepository */
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            $labels,
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }

    private function createMediaFileAttributeAsMainMedia(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        string $identifier,
        array $labels,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) {
        /** @var SqlAssetFamilyRepository $assetFamilyRepository */
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        /** @var SqlAttributeRepository $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::fromString($identifier),
            $assetFamilyIdentifier,
            AttributeCode::fromString($identifier),
            LabelCollection::fromArray($labels),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            $valuePerChannel,
            $valuePerLocale,
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );

        $attributeRepository->create($mediaFileAttribute);

        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier(AttributeIdentifier::fromString($identifier)));
        $assetFamilyRepository->update($assetFamily);
    }

    private function createMediaLinkAttributeAsMainMedia(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        string $identifier,
        array $labels,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) {
        /** @var SqlAssetFamilyRepository $assetFamilyRepository */
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        /** @var SqlAttributeRepository $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $mediaLinkAttribute = MediaLinkAttribute::create(
            AttributeIdentifier::fromString($identifier),
            $assetFamilyIdentifier,
            AttributeCode::fromString($identifier),
            LabelCollection::fromArray($labels),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            $valuePerChannel,
            $valuePerLocale,
            Prefix::empty(),
            Suffix::empty(),
            MediaType::fromString(MediaType::PDF)
        );

        $attributeRepository->create($mediaLinkAttribute);

        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier(AttributeIdentifier::fromString($identifier)));
        $assetFamilyRepository->update($assetFamily);
    }

    private function createAsset(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        string $code,
        array $values
    ) {
        /** @var SqlAssetRepository $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $assetCode = AssetCode::fromString($code);
        $assetIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $this->identifiers[$code] = $assetIdentifier;

        $asset = Asset::create(
            $assetIdentifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues(array_map(function (array $value) {
                $type = $value['type'];
                return Value::create(
                    AttributeIdentifier::fromString($value['attribute']),
                    ChannelReference::createFromNormalized($value['channel']),
                    LocaleReference::createFromNormalized($value['locale']),
                    $type::createFromNormalize($value['data'])
                );
            }, $values))
        );

        $assetRepository->create($asset);
    }
}

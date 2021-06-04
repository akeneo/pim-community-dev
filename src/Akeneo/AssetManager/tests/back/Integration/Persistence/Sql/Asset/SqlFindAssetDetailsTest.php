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
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindAssetDetailsTest extends SqlIntegrationTestCase
{
    private FindAssetDetailsInterface $findAssetDetailsQuery;

    private AssetRepositoryInterface $assetRepository;

    private ?AssetIdentifier $assetIdentifier = null;

    private AttributeRepositoryInterface $attributeRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAssetDetailsQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_details');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_assets()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown_asset_family');
        $assetCode = AssetCode::fromString('unknown_asset_code');
        $this->assertNull($this->findAssetDetailsQuery->find($assetFamilyIdentifier, $assetCode));
    }

    /**
     * @test
     */
    public function it_returns_the_asset_details()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        $assetCode = AssetCode::fromString('starck');
        $actualStarck = $this->findAssetDetailsQuery->find($assetFamilyIdentifier, $assetCode);
        $nameAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'name', 'fingerprint')
        );
        $descriptionAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'description', 'fingerprint')
        );
        $labelAttribute = $this->attributeRepository->getByIdentifier(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier()
        );
        $mediaFileAttribute = $this->attributeRepository->getByIdentifier(
            $assetFamily->getAttributeAsMainMediaReference()->getIdentifier()
        );

        $expectedValues = [
            [
                'data'      => null,
                'locale'    => 'de_DE',
                'channel'   => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data'      => null,
                'locale'    => 'en_US',
                'channel'   => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data'      => null,
                'locale'    => 'fr_FR',
                'channel'   => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data'      => 'Hello',
                'locale'    => null,
                'channel'   => null,
                'attribute' => $nameAttribute->normalize(),
            ],
            [
                'data'      => 'Philippe Starck',
                'locale'    => 'fr_FR',
                'channel'   => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data'      => null,
                'locale'    => 'en_US',
                'channel'   => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data'      => null,
                'locale'    => 'de_DE',
                'channel'   => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data'      => [
                    'filePath'         => 'test/image_2.jpg',
                    'originalFilename' => 'image_2.jpg',
                    'size'             => 100,
                    'mimeType'         => 'image/jpg',
                    'extension'        => '.jpg',
                    'updatedAt'        => '2019-11-22T15:16:21+0000',
                ],
                'locale'    => null,
                'channel'   => null,
                'attribute' => $mediaFileAttribute->normalize(),
            ],
        ];

        $expectedStarck = new AssetDetails(
            $this->assetIdentifier,
            $assetFamilyIdentifier,
            $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
            $assetCode,
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            [
                [
                    'data'      => [
                        'size'             => 100,
                        'filePath'         => 'test/image_2.jpg',
                        'mimeType'         => 'image/jpg',
                        'extension'        => '.jpg',
                        'updatedAt'        => '2019-11-22T15:16:21+0000',
                        'originalFilename' => 'image_2.jpg',
                    ],
                    'locale'    => null,
                    'channel'   => null,
                    'attribute' => $mediaFileAttribute->normalize(),
                ],
            ],
            $expectedValues,
            true
        );

        $this->assertAssetDetails($expectedStarck, $actualStarck);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $labelValue = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck')
        );
        $imageValue = Value::create(
            $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromNormalize([
                'filePath' => 'test/image_2.jpg',
                'originalFilename' => 'image_2.jpg',
                'size' => 100,
                'mimeType' => 'image/jpg',
                'extension' => '.jpg',
                'updatedAt' => '2019-11-22T15:16:21+0000',
            ])
        );

        $value = Value::create(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Hello')
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($textAttribute);

        $localizedTextAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'description']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(2500),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($localizedTextAttribute);

        $starckCode = AssetCode::fromString('starck');
        $this->assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $starckCode);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $this->assetRepository->create(
            Asset::create(
                $this->assetIdentifier,
                $assetFamilyIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValue, $imageValue, $value])
            )
        );
    }

    private function assertAssetDetails(AssetDetails $expected, AssetDetails $actual): void
    {
        $normalizeExpectedValues = $expected->normalize();
        $normalizeActualValues = $actual->normalize();
        $this->assertEquals($normalizeExpectedValues['identifier'], $normalizeActualValues['identifier']);
        $this->assertEquals($normalizeExpectedValues['asset_family_identifier'], $normalizeActualValues['asset_family_identifier']);
        $this->assertEquals($normalizeExpectedValues['attribute_as_main_media_identifier'], $normalizeActualValues['attribute_as_main_media_identifier']);
        $this->assertEquals($normalizeExpectedValues['code'], $normalizeActualValues['code']);
        $this->assertEquals($normalizeExpectedValues['labels'], $normalizeActualValues['labels']);
        $this->assertEqualsCanonicalizing($normalizeExpectedValues['values'], $normalizeActualValues['values']);
        $this->assertEquals($normalizeExpectedValues['image'], $normalizeActualValues['image']);
        $this->assertEquals($normalizeExpectedValues['permission'], $normalizeActualValues['permission']);
    }
}

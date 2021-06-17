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
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationAsset;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindTransformationAssetsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Webmozart\Assert\Assert;

class SqlFindTransformationAssetsByIdentifiersTest extends SqlIntegrationTestCase
{
    private AssetRepositoryInterface $repository;

    private FindTransformationAssetsByIdentifiersInterface $findTransformationAssetsQuery;

    private SaverInterface $fileInfoSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->findTransformationAssetsQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_transformation_assets_by_identifiers');
        $this->fileInfoSaver = $this->get('akeneo_file_storage.saver.file');

        $this->resetDB();
        $this->loadAssetFamilyWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_assets_from_a_list_of_identifiers()
    {
        $this->loadAssets(['starck', 'dyson', 'newson']);
        $this->loadAssets(['unexpected_asset']);

        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedTransformationAssets = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedTransformationAssets[sprintf('designer_%s_fingerprint', $code)] = new TransformationAsset(
                AssetIdentifier::fromString(sprintf('designer_%s_fingerprint', $code)),
                AssetCode::fromString($code),
                AssetFamilyIdentifier::fromString('designer'),
                [
                    'name_designer_fingerprint_print_en_US' => [
                        'data' => sprintf('Name: %s for print channel', $code),
                        'locale' => 'en_US',
                        'channel' => 'print',
                        'attribute' => 'name_designer_fingerprint',
                    ],
                    'name_designer_fingerprint_ecommerce_en_US' => [
                        'data' => sprintf('Name: %s', $code),
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'attribute' => 'name_designer_fingerprint',
                    ],
                    'name_designer_fingerprint_ecommerce_fr_FR' => [
                        'data' => sprintf('Nom: %s', $code),
                        'locale' => 'fr_FR',
                        'channel' => 'ecommerce',
                        'attribute' => 'name_designer_fingerprint',
                    ]
                ]
            );
        }

        $transformationAssetsFound = $this->findTransformationAssetsQuery->find($identifiers);

        $this->assertSameTransformationAssets($expectedTransformationAssets, $transformationAssetsFound);
    }

    /**
     * @param TransformationAsset[] $expectedTransformationAssets
     * @param TransformationAsset[] $transformationAssetsFound
     */
    private function assertSameTransformationAssets(array $expectedTransformationAssets, array $transformationAssetsFound): void
    {
        $this->assertCount(count($expectedTransformationAssets), $transformationAssetsFound);

        foreach ($expectedTransformationAssets as $index => $connectorAsset) {
            $this->assertSameTransformationAsset($connectorAsset, $transformationAssetsFound[$index]);
        }
    }

    private function assertSameTransformationAsset(TransformationAsset $expectedAsset, TransformationAsset $currentAsset): void
    {
        Assert::true($expectedAsset->getCode()->equals($currentAsset->getCode()));
        Assert::true($expectedAsset->getAssetFamilyIdentifier()->equals($currentAsset->getAssetFamilyIdentifier()));
        Assert::true($expectedAsset->getIdentifier()->equals($currentAsset->getIdentifier()));
        $this->assertSame($expectedAsset->getRawValueCollection(), $currentAsset->getRawValueCollection());
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @param string[] $codes
     */
    private function loadAssets(array $codes): void
    {
        foreach ($codes as $code) {
            $asset = Asset::create(
                AssetIdentifier::fromString(sprintf('designer_%s_fingerprint', $code)),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString($code),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s for print channel', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString(sprintf('Nom: %s', $code))
                    )
                ])
            );

            $assets[] = $asset;
            $this->repository->create($asset);
        }
    }

    private function loadAssetFamilyWithAttributes(): void
    {
        $repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $repository->create($assetFamily);

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
            MediaType::fromString(MediaType::IMAGE)
        );

        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
    }
}

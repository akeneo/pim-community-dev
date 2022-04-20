<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Query\Structure;

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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductAssetAndAttributesInfo;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use PHPUnit\Framework\Assert;

final class GetProductAssetAndAttributesInfoIntegration extends DataQualityInsightsTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.repository.asset_family'
        );
        $this->get('feature_flags')->enable('asset_manager');
    }

    public function test_it_returns_product_asset_and_attributes_info_from_product_family_code_list(): void
    {
        // Create AssetFamily and Asset Attribute with MediaFile image type by default
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute('mediaFileImage');
        $this->createAttribute(
            'asset_collection',
            [
                'type' => AttributeTypes::ASSET_COLLECTION,
                'reference_data_name' => 'mediaFileImage'
            ]
        );

        // Create AssetFamily and Asset Attribute with another attribute type media link image
        $this->createAssetFamilyWithMediaLinkAttribute('mediaLinkImage', MediaLinkType::IMAGE);
        $this->createAttribute(
            'asset_collection_link',
            [
                'type' => AttributeTypes::ASSET_COLLECTION,
                'reference_data_name' => 'mediaLinkImage'
            ]
        );

        $this->createFamily('family_product', ['attributes' => ['asset_collection', 'asset_collection_link']]);

        $this->createProduct('product', ['family' => 'family_product']);

        $result = $this
            ->get(GetProductAssetAndAttributesInfo::class)
            ->forProductFamilyCodes(['family_product', 'unknown_family_product']);

        // Check if result not empty
        Assert::assertCount(1, $result, 'missing asset collection found for product family codes');
        Assert::assertArrayHasKey('family_product', $result, "no asset collection for product family");

        // Check the return array structure
        Assert::assertArrayHasKey('attribute_code', $result['family_product'][0], "no attribute_code key");
        Assert::assertArrayHasKey('asset_family_identifier', $result['family_product'][0], "no asset_family_identifier key");
        Assert::assertArrayHasKey('attribute_code', $result['family_product'][1], "no attribute_code key");
        Assert::assertArrayHasKey('asset_family_identifier', $result['family_product'][1], "no asset_family_identifier key");

        // Valid the data
        Assert::assertEquals('asset_collection', $result['family_product'][0]['attribute_code']);
        Assert::assertStringContainsString('mediaFileImage', $result['family_product'][0]['asset_family_identifier']);
        Assert::assertEquals('asset_collection_link', $result['family_product'][1]['attribute_code']);
        Assert::assertStringContainsString('mediaLinkImage', $result['family_product'][1]['asset_family_identifier']);
    }

    public function test_it_returns_empty_info_from_product_family_code_unknown_list(): void
    {
        $result = $this
            ->get(GetProductAssetAndAttributesInfo::class)
            ->forProductFamilyCodes(['family_product', 'unknown_family_product']);

        Assert::assertEmpty($result, 'missing asset collection found for product family codes');
    }

    private function createAssetFamilyWithMediaFileAsMainMediaAttribute(string $assetFamilyIdentifier): AssetFamily
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $this->assetFamilyRepository->create(
                AssetFamily::create(
                    $assetFamilyIdentifier,
                    [],
                    Image::createEmpty(),
                    RuleTemplateCollection::empty()
                )
            );

        return $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
    }

    private function createAssetFamilyWithMediaLinkAttribute(
        string $assetFamilyIdentifier,
        string $mediaType,
        bool   $hasMainMedia = false
    ) {
        $assetFamily = $this->createAssetFamilyWithMediaFileAsMainMediaAttribute($assetFamilyIdentifier);

        $mediaLinkIdentifier = AttributeIdentifier::create(
            (string)$assetFamily->getIdentifier(),
            'assetLinkAttributeCode',
            'fingerprint'
        );

        $mediaLinkAttribute = MediaLinkAttribute::create(
            $mediaLinkIdentifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('url'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString(null),
            Suffix::fromString(null),
            MediaLinkType::fromString($mediaType)
        );

        $assetAttributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $assetAttributeRepository->create($mediaLinkAttribute);
        if ($hasMainMedia) {
            $assetFamily->updateAttributeAsMainMediaReference(
                AttributeAsMainMediaReference::fromAttributeIdentifier($mediaLinkIdentifier)
            );
        }
        $this->assetFamilyRepository->update($assetFamily);
    }

}

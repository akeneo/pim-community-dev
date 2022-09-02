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

namespace Akeneo\AssetManager\Integration\PublicApi\Platform;

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
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAssetAttributesWithMediaInfo;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

final class SqlGetAssetAttributesWithMediaInfoTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;
    private SqlGetAssetAttributesWithMediaInfo $getAssetAttributesWithMediaInfo;

    public function setUp(): void
    {
        parent::setUp();
        $this->getAssetAttributesWithMediaInfo = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.query.platform.get_asset_attribute_with_media_info_public_api'
        );
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_all_asset_information_from_asset_family_identifier_list(): void
    {
        // Create AssetFamily and Asset Attribute with MediaFile image type by default
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute('mediaFileImage');

        // Create AssetFamily and Asset Attribute with another attribute type media link image
        $this->createAssetFamilyWithMediaLinkAttribute(
            'mediaLinkImage',
            MediaLinkType::IMAGE
        );

        $result = $this->getAssetAttributesWithMediaInfo->forFamilyIdentifiers(['mediaFileImage', 'mediaLinkImage']);

        // Check if result not empty
        Assert::assertCount(2, $result, 'No asset attribute found');

        // Check array structure result
        Assert::assertArrayHasKey('identifier', $result[0], "no identifier key");
        Assert::assertArrayHasKey('attribute_as_main_media', $result[0], "no attribute_as_main_media key");
        Assert::assertArrayHasKey('attribute_type', $result[0], "no attribute_type key");
        Assert::assertArrayHasKey('media_type', $result[0], "no media_type key");
        Assert::assertArrayHasKey('identifier', $result[1], "no identifier key");
        Assert::assertArrayHasKey('attribute_as_main_media', $result[1], "no attribute_as_main_media key");
        Assert::assertArrayHasKey('attribute_type', $result[1], "no attribute_type key");
        Assert::assertArrayHasKey('media_type', $result[1], "no media_type key");
    }

    /**
     * @test
     */
    public function it_returns_no_asset_information_from_unknown_asset_family_identifier_list(): void
    {
        // Create AssetFamily and Asset Attribute with MediaFile image type by default
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute('mediaFileImage');

        // Create AssetFamily and Asset Attribute with another attribute type media link image
        $this->createAssetFamilyWithMediaLinkAttribute(
            'mediaLinkImage',
            MediaLinkType::IMAGE
        );

        $result = $this->getAssetAttributesWithMediaInfo->forFamilyIdentifiers(['mediaUnknown']);

        // Check if result not empty
        Assert::assertEmpty($result, 'asset information found from unknown asset family identifier');
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

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $attributeRepository->create($mediaLinkAttribute);
        if ($hasMainMedia) {
            $assetFamily->updateAttributeAsMainMediaReference(
                AttributeAsMainMediaReference::fromAttributeIdentifier($mediaLinkIdentifier)
            );
        }
        $this->assetFamilyRepository->update($assetFamily);
    }
}

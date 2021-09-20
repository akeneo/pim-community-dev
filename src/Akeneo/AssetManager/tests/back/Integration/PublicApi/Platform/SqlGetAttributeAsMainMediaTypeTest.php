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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAttributeAsMainMediaType;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

final class SqlGetAttributeAsMainMediaTypeTest extends SqlIntegrationTestCase
{
    private SqlGetAttributeAsMainMediaType $sqlGetAttributeAsMainMediaType;
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAttributeAsMainMediaType = $this->get('akeneo_assetmanager.infrastructure.persistence.query.platform.get_attribute_as_main_media_type_public_api');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /** @test */
    public function it_says_if_an_asset_family_main_media_attribute_is_a_media_file(): void
    {
        $assetFamilyIdentifier = 'packshot';
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute($assetFamilyIdentifier);

        $isMediaFile = $this->sqlGetAttributeAsMainMediaType->isMediaFile($assetFamilyIdentifier);
        self::assertTrue($isMediaFile);
    }

    /** @test */
    public function it_says_if_an_asset_family_main_media_attribute_is_a_media_link(): void
    {
        $assetFamilyIdentifier = 'notice';
        $this->createAssetFamilyWithMediaLinkAsMainMediaAttribute($assetFamilyIdentifier);

        $isMediaLink = $this->sqlGetAttributeAsMainMediaType->isMediaLink($assetFamilyIdentifier);
        self::assertTrue($isMediaLink);
    }

    /** @test */
    public function it_throws_runtine_exception_for_unknown_asset_family(): void
    {
        $assetFamilyIdentifier = 'unknown';
        $expectedRuntimeException = new \RuntimeException(sprintf('Asset family "%s" does not exists', $assetFamilyIdentifier));

        self::expectExceptionObject($expectedRuntimeException);
        $this->sqlGetAttributeAsMainMediaType->isMediaFile($assetFamilyIdentifier);
        self::expectExceptionCode($expectedRuntimeException);
        $this->sqlGetAttributeAsMainMediaType->isMediaLink($assetFamilyIdentifier);
    }

    private function createAssetFamilyWithMediaFileAsMainMediaAttribute(string $assetFamilyIdentifier)
    {
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
    }

    private function createAssetFamilyWithMediaLinkAsMainMediaAttribute(string $assetFamilyIdentifier)
    {
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute($assetFamilyIdentifier);

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
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($mediaLinkIdentifier));
        $this->assetFamilyRepository->update($assetFamily);
    }
}

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
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAttributeAsMainMedia;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

final class SqlGetAttributeAsMainMediaTest extends SqlIntegrationTestCase
{
    private SqlGetAttributeAsMainMedia $sqlGetAttributeAsMainMedia;
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAttributeAsMainMedia = $this->get('akeneo_assetmanager.infrastructure.persistence.query.platform.get_attribute_as_main_media_public_api');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /** @test */
    public function it_returns_the_main_media_of_an_asset_family(): void
    {
        $this->createAssetFamilyWithMediaFileAsMainMediaAttribute('media_file_as_main_media');
        $mainMedia = $this->sqlGetAttributeAsMainMedia->forAssetFamilyCode('media_file_as_main_media');
        self::assertTrue($mainMedia->isMediaFile());
        self::assertFalse($mainMedia->isMediaLink());

        $this->createAssetFamilyWithMediaLinkAsMainMediaAttribute('media_link_as_main_media', false, false);
        $mainMedia = $this->sqlGetAttributeAsMainMedia->forAssetFamilyCode('media_link_as_main_media');
        self::assertTrue($mainMedia->isMediaLink());
        self::assertFalse($mainMedia->isMediaFile());
        self::assertFalse($mainMedia->isScopable());
        self::assertFalse($mainMedia->isLocalizable());

        $this->createAssetFamilyWithMediaLinkAsMainMediaAttribute('scopable_and_localizable_media_link_as_main_media', true, true);
        $mainMedia = $this->sqlGetAttributeAsMainMedia->forAssetFamilyCode('scopable_and_localizable_media_link_as_main_media');
        self::assertTrue($mainMedia->isMediaLink());
        self::assertFalse($mainMedia->isMediaFile());
        self::assertTrue($mainMedia->isScopable());
        self::assertTrue($mainMedia->isLocalizable());
    }

    /** @test */
    public function it_throws_a_runtime_exception_for_unknown_asset_family(): void
    {
        $assetFamilyIdentifier = 'unknown';
        $expectedRuntimeException = new \RuntimeException(sprintf('Asset family "%s" does not exists', $assetFamilyIdentifier));

        self::expectExceptionObject($expectedRuntimeException);
        $this->sqlGetAttributeAsMainMedia->forAssetFamilyCode($assetFamilyIdentifier);
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

    private function createAssetFamilyWithMediaLinkAsMainMediaAttribute(
        string $assetFamilyIdentifier,
        bool $valuePerChannel,
        bool $valuePerLocale
    ): void {
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
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString(MediaType::PDF)
        );

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $attributeRepository->create($mediaLinkAttribute);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($mediaLinkIdentifier));
        $this->assetFamilyRepository->update($assetFamily);
    }
}

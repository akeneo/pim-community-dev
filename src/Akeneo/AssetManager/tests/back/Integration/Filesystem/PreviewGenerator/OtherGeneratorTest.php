<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\OtherGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class OtherGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    private PreviewGeneratorInterface $otherGenerator;

    private MediaLinkAttribute $mediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->otherGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.other_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_media_type_other_of_an_url_and_media_file_attribute()
    {
        $mediaFileAttributeWithoutOther = $this->createAttributeMediaFileWithMediaType(MediaType::IMAGE);
        $mediaFileAttributeWithOther = $this->createAttributeMediaFileWithMediaType(MediaType::OTHER);

        $isSupportedMediaLinkAttribute = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $this->mediaLinkAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );
        $isSupportedMediaFileWithOther = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $mediaFileAttributeWithOther,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );
        $isSupportedMediaFileWithoutOther = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $mediaFileAttributeWithoutOther,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );

        $this->assertTrue($isSupportedMediaLinkAttribute);
        $this->assertTrue($isSupportedMediaFileWithOther);
        $this->assertFalse($isSupportedMediaFileWithoutOther);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_url_and_media_file_attribute()
    {
        $mediaFileAttribute = $this->createAttributeMediaFileWithMediaType(MediaType::OTHER);
        $isSupported = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $mediaFileAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );
        $this->assertTrue($isSupported);

        $isSupported = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $this->mediaLinkAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );
        $this->assertTrue($isSupported);

        $isSupported = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $this->mediaLinkAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE
        );
        $this->assertTrue($isSupported);

        $isSupported = $this->otherGenerator->supports(self::IMAGE_FILENAME,
            $this->mediaLinkAttribute,
            PreviewGeneratorRegistry::PREVIEW_TYPE
        );
        $this->assertTrue($isSupported);

        $isSupported = $this->otherGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, 'wrong_type');
        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_default_image()
    {
        $this->otherGenerator->supports('test', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->otherGenerator->generate('test',
            $this->mediaLinkAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.other',
                OtherGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]
            ),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['video'])
            ->load();
        $this->mediaLinkAttribute = $fixtures['attributes']['video'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                'video' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'the-amazing-video.mov',
                    ],
                ],
            ]
            )
            ->load();
    }

    private function createAttributeMediaFileWithMediaType(string $mediaType): MediaFileAttribute
    {
        return MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'media_file_attribute', 'finger'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('media_file_attribute'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('150.110'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString($mediaType)
        );
    }
}

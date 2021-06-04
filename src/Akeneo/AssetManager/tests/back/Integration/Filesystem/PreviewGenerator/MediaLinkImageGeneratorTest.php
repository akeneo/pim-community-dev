<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkImageGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class MediaLinkImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    private PreviewGeneratorInterface $mediaLinkImageGenerator;

    private MediaLinkAttribute $mediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaLinkImageGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.media_link_image_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_media_type_image_of_an_media_link_attribute()
    {
        $isSupported = $this->mediaLinkImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_media_link_attribute()
    {
        $isSupported = $this->mediaLinkImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaLinkAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute()
    {
        $this->mediaLinkImageGenerator->supports('2016/04/Ben-site-web.jpg', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute_from_the_cache()
    {
        $this->mediaLinkImageGenerator->supports('2016/04/Site-web-Nico.jpg', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $previewImage = $this->mediaLinkImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->mediaLinkImageGenerator->supports('test', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate('test', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $type = MediaLinkImageGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE];
        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', $type),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                 'website'
            ])
            ->load();
        $this->mediaLinkAttribute = $fixtures['attributes']['website'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'website' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => self::IMAGE_FILENAME,
                     ]
                 ]
            ])
            ->load();
    }
}

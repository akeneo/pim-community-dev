<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkYoutubeGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class MediaLinkYoutubeGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    protected const YOUTUBE_VIDEO_ID = 'youtube-id';

    private PreviewGeneratorInterface $mediaLinkYoutubeGenerator;

    private MediaLinkAttribute $youtubeMediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaLinkYoutubeGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.media_link_youtube_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_media_link_attribute()
    {
        $isSupported = $this->mediaLinkYoutubeGenerator->supports(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkYoutubeGenerator->supports(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkYoutubeGenerator->supports(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkYoutubeGenerator->supports(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, 'wrong_type');
        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute()
    {
        $previewImage = $this->mediaLinkYoutubeGenerator->generate(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute_from_the_cache()
    {
        $previewImage = $this->mediaLinkYoutubeGenerator->generate(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $previewImage = $this->mediaLinkYoutubeGenerator->generate(self::YOUTUBE_VIDEO_ID, $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->mediaLinkYoutubeGenerator->supports('test', $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkYoutubeGenerator->generate('test', $this->youtubeMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $type = MediaLinkYoutubeGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE];
        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', $type),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['youtube'])
            ->load();
        $this->youtubeMediaLinkAttribute = $fixtures['attributes']['youtube'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'youtube' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => self::YOUTUBE_VIDEO_ID,
                     ]
                 ]
            ])
            ->load();
    }
}

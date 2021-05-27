<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkVimeoGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class MediaLinkVimeoGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    protected const VIMEO_VIDEO_ID = '543643';

    private PreviewGeneratorInterface $mediaLinkVimeoGenerator;

    private MediaLinkAttribute $vimeoMediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaLinkVimeoGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.media_link_vimeo_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_media_link_attribute()
    {
        $isSupported = $this->mediaLinkVimeoGenerator->supports(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkVimeoGenerator->supports(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkVimeoGenerator->supports(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkVimeoGenerator->supports(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, 'wrong_type');
        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute()
    {
        $previewImage = $this->mediaLinkVimeoGenerator->generate(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute_from_the_cache()
    {
        $previewImage = $this->mediaLinkVimeoGenerator->generate(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $previewImage = $this->mediaLinkVimeoGenerator->generate(self::VIMEO_VIDEO_ID, $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->mediaLinkVimeoGenerator->supports('something', $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkVimeoGenerator->generate('something', $this->vimeoMediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $type = MediaLinkVimeoGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE];
        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', $type),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['vimeo'])
            ->load();
        $this->vimeoMediaLinkAttribute = $fixtures['attributes']['vimeo'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'vimeo' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => self::VIMEO_VIDEO_ID,
                     ]
                 ]
            ])
            ->load();
    }
}

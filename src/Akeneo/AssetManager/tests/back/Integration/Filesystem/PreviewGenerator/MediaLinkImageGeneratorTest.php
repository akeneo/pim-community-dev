<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkImageGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class MediaLinkImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $mediaLinkImageGenerator;

    /** @var MediaLinkAttribute */
    private $mediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaLinkImageGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.mediaLink_image_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_media_type_image_of_an_mediaLink_attribute()
    {
        $isSupported = $this->mediaLinkImageGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_mediaLink_attribute()
    {
        $isSupported = $this->mediaLinkImageGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkImageGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_mediaLink_attribute()
    {
        $this->mediaLinkImageGenerator->supports('google-logo.png', $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_mediaLink_attribute_from_the_cache()
    {
        $this->mediaLinkImageGenerator->supports('akeneo.png', $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->mediaLinkImageGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->mediaLinkImageGenerator->supports('test', $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkImageGenerator->generate('test', $this->mediaLinkAttribute, MediaLinkImageGenerator::THUMBNAIL_TYPE);

        $type = MediaLinkImageGenerator::SUPPORTED_TYPES[MediaLinkImageGenerator::THUMBNAIL_TYPE];
        $this->assertStringContainsString(
            sprintf('media/cache/%s/pim_asset_manager.default_image.image', $type),
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
                         'data' => self::FILENAME,
                     ]
                 ]
            ])
            ->load();
    }
}

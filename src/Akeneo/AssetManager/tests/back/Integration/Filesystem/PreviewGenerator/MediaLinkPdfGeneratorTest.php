<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkPdfGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class MediaLinkPdfGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    protected const FILENAME = '2016/04/Fred-site-web.pdf';

    /** @var PreviewGeneratorInterface */
    private $mediaLinkPdfGenerator;

    /** @var MediaLinkAttribute */
    private $mediaLinkAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaLinkPdfGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.media_link_pdf_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_media_link_attribute()
    {
        $isSupported = $this->mediaLinkPdfGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkPdfGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkPdfGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->mediaLinkPdfGenerator->supports(self::FILENAME, $this->mediaLinkAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute()
    {
        $previewImage = $this->mediaLinkPdfGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_media_link_attribute_from_the_cache()
    {
        $previewImage = $this->mediaLinkPdfGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->mediaLinkPdfGenerator->generate(self::FILENAME, $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->mediaLinkPdfGenerator->supports('test', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->mediaLinkPdfGenerator->generate('test', $this->mediaLinkAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $type = MediaLinkPdfGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE];
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
                 'notice'
            ])
            ->load();
        $this->mediaLinkAttribute = $fixtures['attributes']['notice'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'notice' => [
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

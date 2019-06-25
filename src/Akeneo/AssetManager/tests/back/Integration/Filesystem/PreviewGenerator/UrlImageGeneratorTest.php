<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\UrlImageGenerator;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class UrlImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $urlImageGenerator;

    /** @var UrlAttribute */
    private $urlAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlImageGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.url_image_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_media_type_image_of_an_url_attribute()
    {
        $isSupported = $this->urlImageGenerator->supports(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_url_attribute()
    {
        $isSupported = $this->urlImageGenerator->supports(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->urlImageGenerator->supports(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->urlImageGenerator->supports(self::FILENAME, $this->urlAttribute, UrlImageGenerator::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->urlImageGenerator->supports(self::FILENAME, $this->urlAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_url_attribute()
    {
        $this->urlImageGenerator->supports('google-logo.png', $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->urlImageGenerator->generate(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_url_attribute_from_the_cache()
    {
        $this->urlImageGenerator->supports('akeneo.png', $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->urlImageGenerator->generate(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->urlImageGenerator->generate(self::FILENAME, $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_url()
    {
        $this->urlImageGenerator->supports('test', $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->urlImageGenerator->generate('test', $this->urlAttribute, UrlImageGenerator::THUMBNAIL_TYPE);

        $type = UrlImageGenerator::SUPPORTED_TYPES[UrlImageGenerator::THUMBNAIL_TYPE];
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
        $this->urlAttribute = $fixtures['attributes']['website'];

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

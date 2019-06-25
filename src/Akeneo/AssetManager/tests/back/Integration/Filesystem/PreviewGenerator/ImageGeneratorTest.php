<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $imageGenerator;

    /** @var MediaLinkAttribute */
    private $attribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->imageGenerator = $this->get('akeneo_assetmanager.application.generator.image_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_media_type_image_of_an_mediaLink_attribute()
    {
        $isSupported = $this->imageGenerator->supports(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_mediaLink_attribute()
    {
        $isSupported = $this->imageGenerator->supports(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->imageGenerator->supports(self::FILENAME, $this->attribute, 'preview');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_mediaLink_attribute()
    {
        $this->imageGenerator->supports('google-logo.png', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        
        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_mediaLink_attribute_from_the_cache()
    {
        $this->imageGenerator->supports('akeneo.png', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->imageGenerator->generate(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_mediaLink()
    {
        $this->imageGenerator->supports('test', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate('test', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(sprintf('media/cache/%s/pim_asset_file_image_default_image', PreviewGeneratorRegistry::THUMBNAIL_TYPE), $previewImage);
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                'website'
             ])
            ->load();
        $this->attribute = $fixtures['attributes']['website'];

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

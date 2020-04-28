<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\BinaryImageGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class BinaryImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $binaryImageGenerator;

    /** @var MediaFileAttribute */
    private $mediaFileAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->binaryImageGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.binary_image_generator');
        $this->loadFixtures();
    }
    /**
     * @test
     */
    public function it_can_support_only_media_file_attribute()
    {
        $isSupported = $this->binaryImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_types_of_a_media_file_attribute()
    {
        $isSupported = $this->binaryImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::IMAGE_FILENAME, $this->mediaFileAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute()
    {
        $this->binaryImageGenerator->supports('google-logo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute_from_the_cache()
    {
        $this->binaryImageGenerator->supports('akeneo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $previewImage = $this->binaryImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute_from_the_cache_removed()
    {
        $this->binaryImageGenerator->supports('akeneo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $this->binaryImageGenerator->remove(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $previewImage = $this->binaryImageGenerator->generate(self::IMAGE_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_media_link()
    {
        $this->binaryImageGenerator->supports('test', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate('test', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', BinaryImageGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                 'main_image'
            ])
            ->load();
        $this->mediaFileAttribute = $fixtures['attributes']['main_image'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'main_image' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => [
                             'filePath' => self::IMAGE_FILENAME,
                             'originalFilename' => self::IMAGE_FILENAME,
                             'size' => 12,
                             'mimeType' => 'image/png',
                             'extension' => '.png',
                             'updatedAt' => '2019-11-22T15:16:21+0000',
                         ],
                     ]
                 ]
            ])
            ->load();
    }
}

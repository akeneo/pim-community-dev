<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\BinaryImageGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\CouldNotGeneratePreviewException;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;

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

    /** @var FileStorer */
    private $fileStorer;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
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
        $data = $this->generateImage(10, 1);
        $this->binaryImageGenerator->supports('google-logo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/am_binary_image_thumbnail/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/asset_manager/am_binary_image_thumbnail/pim_asset_manager.default_image.image',
            $previewImage
        );
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute_from_the_cache()
    {
        $data = $this->generateImage(10, 1);
        $this->binaryImageGenerator->supports('akeneo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/am_binary_image_thumbnail/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/asset_manager/am_binary_image_thumbnail/pim_asset_manager.default_image.image',
            $previewImage
        );

        $newPreviewImage = $this->binaryImageGenerator->generate($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertEquals($previewImage, $newPreviewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute_from_the_cache_removed()
    {
        $data = $this->generateImage(10, 1);

        $this->binaryImageGenerator->supports('akeneo.png', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/am_binary_image_thumbnail/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/asset_manager/am_binary_image_thumbnail/pim_asset_manager.default_image.image',
            $previewImage
        );

        $this->binaryImageGenerator->remove($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $newPreviewImage = $this->binaryImageGenerator->generate($data, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertEquals($previewImage, $newPreviewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/asset_manager/am_binary_image_thumbnail/pim_asset_manager.default_image.image',
            $newPreviewImage
        );
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_media_link()
    {
        $this->binaryImageGenerator->supports('test', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(base64_encode('test'), $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', BinaryImageGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]),
            $previewImage
        );
    }

    /**
     * @test
     */
    public function it_throw_an_error_when_the_file_size_is_too_big()
    {
        $data = $this->generateImage(15000, 100);

        $this->expectException(CouldNotGeneratePreviewException::class);

        $this->binaryImageGenerator->generate(
            $data,
            $this->mediaFileAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
        );
    }

    /**
     * @test
     */
    public function it_throw_an_error_when_resolution_is_too_big()
    {
        $data = $this->generateImage(16000, 1);

        $this->expectException(CouldNotGeneratePreviewException::class);

        $this->binaryImageGenerator->generate(
            $data,
            $this->mediaFileAttribute,
            PreviewGeneratorRegistry::THUMBNAIL_TYPE
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

    private function generateImage(int $size, int $quality): string
    {
        $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_image.jpg';
        $image = imagecreate($size, $size);
        self::assertTrue(imagejpeg($image, $imageFilename, $quality));
        $fileInfo = $this->fileStorer->store(new \SplFileInfo($imageFilename), Storage::FILE_STORAGE_ALIAS);

        return base64_encode($fileInfo->getKey());
    }
}

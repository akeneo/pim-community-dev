<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\FileSystem\PreviewGenerator;

use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\BinaryImageGenerator;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\CouldNotGeneratePreviewException;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BinaryImageGeneratorIntegration extends TestCase
{
    private PreviewGeneratorInterface $binaryImageGenerator;
    private AttributeImage $attributeImage;
    private FileInfoInterface $fileInfo;

    public function setUp(): void
    {
        parent::setUp();
        $this->binaryImageGenerator = $this->get(BinaryImageGenerator::class);
        $this->attributeImage = AttributeImage::create(
            AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
            new AttributeCode('banner_image'),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Banner image']),
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            AttributeAdditionalProperties::fromArray([])
        );
        $this->fileInfo = $this->storeAkeneoImage();
    }

    /** @test */
    public function it_can_support_only_image_attribute(): void
    {
        $isSupported = $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $this->assertTrue($isSupported);
    }

    /** @test */
    public function it_can_support_only_supported_types_of_an_image_attribute(): void
    {
        $isSupported = $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::PREVIEW_TYPE);
        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, 'wrong_type');
        $this->assertFalse($isSupported);
    }

    /** @test */
    public function it_gets_a_preview_for_an_image_attribute(): void
    {
        $data = $this->generateJpegImage(10, 1);

        $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/category/am_binary_image_thumbnail_category/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/category/am_binary_image_thumbnail/pim_category.default_image.image',
            $previewImage
        );
    }

    /** @test */
    public function it_gets_a_preview_for_an_image_attribute_from_the_cache(): void
    {
        $data = $this->generateJpegImage(10, 1);

        $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/category/am_binary_image_thumbnail_category/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/category/am_binary_image_thumbnail/pim_category.default_image.image',
            $previewImage
        );

        $newPreviewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $this->assertEquals($previewImage, $newPreviewImage);
    }

    /** @test */
    public function it_gets_a_preview_for_an_image_attribute_from_the_cache_removed(): void
    {
        $data = $this->generateJpegImage(10, 1);

        $this->binaryImageGenerator->supports(base64_encode($this->fileInfo->getOriginalFilename()), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/category/am_binary_image_thumbnail_category/', $previewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/category/am_binary_image_thumbnail/pim_category.default_image.image',
            $previewImage
        );

        $this->binaryImageGenerator->remove($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $newPreviewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertEquals($previewImage, $newPreviewImage);
        $this->assertNotEquals(
            '__root__/thumbnail/category/am_binary_image_thumbnail_category/pim_category.default_image.image',
            $newPreviewImage
        );
    }

    /** @test */
    public function it_gets_a_default_preview_for_an_unknown_image(): void
    {
        $this->binaryImageGenerator->supports('test', $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(base64_encode('test'), $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/category/%s/pim_category.default_image.image', BinaryImageGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]),
            $previewImage
        );
    }

    /** @test */
    public function it_returns_default_preview_when_the_file_size_is_too_big(): void
    {
        $data = $this->generatePngImage(22000, 0);

        $defaultPreview = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE,);
        $this->assertSame('__root__/thumbnail/category/am_binary_image_thumbnail_category/pim_category.default_image.image', $defaultPreview);
    }

    /** @test */
    public function it_throws_an_error_when_resolution_is_too_big(): void
    {
        $data = $this->generatePngImage(16001, 9);

        $this->expectException(CouldNotGeneratePreviewException::class);
        $this->expectExceptionMessage('Could not load image from string');

        $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
    }

    /** @test */
    public function it_returns_default_image_when_mime_type_is_not_supported(): void
    {
        $data = $this->uploadPdfFile();

        $previewImage = $this->binaryImageGenerator->generate($data, $this->attributeImage, PreviewGeneratorRegistry::THUMBNAIL_TYPE,);

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/category/%s/pim_category.default_image.image', BinaryImageGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]),
            $previewImage,
        );
    }

    private function storeAkeneoImage(): FileInfoInterface
    {
        $fileInfo = new \SplFileInfo($this->getFixturePath('akeneo.jpg'));
        $fileToUpload = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), 'image/jpg');

        return $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($fileToUpload, Storage::CATEGORY_STORAGE_ALIAS);
    }

    private function generateJpegImage(int $size, int $quality): string
    {
        $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_image.jpg';
        $image = imagecreate($size, $size);
        self::assertTrue(imagejpeg($image, $imageFilename, $quality));
        $fileInfo = new \SplFileInfo($imageFilename);
        $fileToUpload = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), 'image/jpg');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($fileToUpload, Storage::CATEGORY_STORAGE_ALIAS);

        return base64_encode($file->getKey());
    }

    private function generatePngImage(int $size, int $quality): string
    {
        $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_image.png';
        $image = imagecreate($size, $size);
        imagecolorallocate($image, 255, 255, 255);
        self::assertTrue(imagepng($image, $imageFilename, $quality));
        $fileInfo = new \SplFileInfo($imageFilename);
        $fileToUpload = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), 'image/png');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($fileToUpload, Storage::CATEGORY_STORAGE_ALIAS);

        return base64_encode($file->getKey());
    }

    private function uploadPdfFile(): string
    {
        $fileInfo = new \SplFileInfo($this->getFixturePath('akeneo.pdf'));
        $fileToUpload = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), 'application/pdf');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($fileToUpload, Storage::CATEGORY_STORAGE_ALIAS);

        return base64_encode($file->getKey());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

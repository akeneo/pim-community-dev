<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryImageDataCleaner;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\Category\Infrastructure\FileSystem\Remover\DeleteFilesFromPaths;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryImageDataCleanerSpec extends ObjectBehavior
{
    public function let(
        DeleteFilesFromPaths $deleteFilesFromPaths,
        PreviewGeneratorInterface $previewGenerator,
    ): void {
        $this->beConstructedWith(
            $deleteFilesFromPaths,
            $previewGenerator,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CategoryImageDataCleaner::class);
    }

    public function it_cleans_category_image_file(
        DeleteFilesFromPaths $deleteFilesFromPaths,
        PreviewGeneratorInterface $previewGenerator
    ): void {
        $imageValue = ImageValue::fromArray([
            'data' => [
                'size' => 168107,
                'extension' => 'jpg',
                'file_path' => 'a_category/shoes.jpg',
                'mime_type' => 'image/jpeg',
                'original_filename' => 'shoes.jpg',
            ],
            'type' => AttributeType::IMAGE,
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'shoes'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c',
        ]);

        $filePath = $imageValue->getValue()->getFilePath();

        foreach (PreviewGeneratorRegistry::IMAGE_TYPES as $type) {
            $previewGenerator->remove(data: base64_encode($filePath), type: $type)->shouldBeCalledOnce();
        }

        $deleteFilesFromPaths->__invoke([$filePath])->shouldBeCalledOnce();

        $this->cleanImageFiles($imageValue);
    }

    public function it_does_not_clean_category_image_file_when_there_is_no_image_data(
        DeleteFilesFromPaths $deleteFilesFromPaths,
        PreviewGeneratorInterface $previewGenerator
    ): void {
        $imageValue = ImageValue::fromArray([
            'data' => null,
            'type' => AttributeType::IMAGE,
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'shoes'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c',
        ]);

        foreach (PreviewGeneratorRegistry::IMAGE_TYPES as $type) {
            $previewGenerator->remove(Argument::type('string'), $type)->shouldNotBeCalled();
        }

        $deleteFilesFromPaths->__invoke([Argument::type('string')])->shouldNotBeCalled();

        $this->cleanImageFiles($imageValue);
    }
}

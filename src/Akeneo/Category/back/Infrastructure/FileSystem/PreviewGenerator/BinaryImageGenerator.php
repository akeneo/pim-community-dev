<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BinaryImageGenerator extends AbstractPreviewGenerator
{
    private const DEFAULT_IMAGE = 'pim_category.default_image.image';
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE => 'am_binary_image_thumbnail_category',
        PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE => 'am_binary_image_thumbnail_small_category',
        PreviewGeneratorRegistry::PREVIEW_TYPE => 'am_binary_image_preview_category',
    ];

    public function supports(string $data, Attribute $attribute, string $type): bool
    {
        return $attribute instanceof AttributeImage
            && (string) $attribute->getType() === AttributeType::IMAGE
            && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    protected function getPreviewType(string $type): string
    {
        return self::SUPPORTED_TYPES[$type];
    }

    protected function defaultImage(): string
    {
        return self::DEFAULT_IMAGE;
    }
}

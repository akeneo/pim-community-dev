<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Akeneo\Category\Domain\Model\Attribute\Attribute;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PreviewGeneratorRegistry implements PreviewGeneratorInterface
{
    /** @var PreviewGeneratorInterface[] */
    private ?array $previewGenerators = null;

    public const THUMBNAIL_TYPE = 'thumbnail';
    public const THUMBNAIL_SMALL_TYPE = 'thumbnail_small';
    public const PREVIEW_TYPE = 'preview';

    public const IMAGE_TYPES = [
        self::THUMBNAIL_TYPE,
        self::THUMBNAIL_SMALL_TYPE,
        self::PREVIEW_TYPE,
    ];

    public function register(PreviewGeneratorInterface $previewGenerator): void
    {
        $this->previewGenerators[] = $previewGenerator;
    }

    public function supportsMimeType(string $mimeType): bool
    {
        return true;
    }

    public function supports(string $data, Attribute $attribute, string $type): bool
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            if ($previewGenerator->supports($data, $attribute, $type)) {
                return true;
            }
        }

        return false;
    }

    public function generate(string $data, Attribute $attribute, string $type): string
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            if ($previewGenerator->supports($data, $attribute, $type)) {
                return $previewGenerator->generate($data, $attribute, $type);
            }
        }

        throw new \RuntimeException(sprintf('There was no generator found to get the preview of attribute "%s" with type "%s"', $attribute->getCode(), $type));
    }

    public function remove(string $data, string $type)
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            return $previewGenerator->remove($data, $type);
        }

        throw new \RuntimeException(sprintf('There was no generator found to remove the preview of attribute "%s" with type "%s"', $data, $type));
    }
}

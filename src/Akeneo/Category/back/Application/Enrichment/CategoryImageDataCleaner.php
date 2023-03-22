<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\Category\Infrastructure\FileSystem\Remover\DeleteFilesFromPaths;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryImageDataCleaner
{
    public function __construct(
        private readonly DeleteFilesFromPaths $deleteFilesFromPaths,
        private readonly PreviewGeneratorInterface $previewGenerator,
    ) {
    }

    public function cleanImageFiles(ImageValue $imageValue): void
    {
        $imageDataValue = $imageValue->getValue();

        if (!$imageDataValue) {
            return;
        }

        foreach (PreviewGeneratorRegistry::IMAGE_TYPES as $type) {
            $this->previewGenerator->remove(
                data: base64_encode($imageDataValue->getFilePath()),
                type: $type,
            );
        }

        ($this->deleteFilesFromPaths)([$imageDataValue->getFilePath()]);
    }
}

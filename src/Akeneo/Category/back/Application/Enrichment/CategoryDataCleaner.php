<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\Filter\ByChannelAndLocalesFilter;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\Category\Infrastructure\FileSystem\Remover\DeleteFilesFromPaths;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryDataCleaner
{
    public function __construct(
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
        private readonly DeleteFilesFromPaths $deleteFilesFromPaths,
        private readonly PreviewGeneratorInterface $previewGenerator,
    ) {
    }

    /**
     * @param array<string, ValueCollection> $valuesByCode
     * @param array<string> $localeCodes
     *
     * @throws \JsonException
     */
    public function cleanByChannelOrLocales(array $valuesByCode, string $channelCode, array $localeCodes): void
    {
        $cleanedEnrichedValues = [];
        foreach ($valuesByCode as $categoryCode => $enrichedValues) {
            $valuesToRemove = ByChannelAndLocalesFilter::getEnrichedValuesToClean(
                $enrichedValues,
                $channelCode,
                $localeCodes,
            );
            if (!empty($valuesToRemove)) {
                foreach ($valuesToRemove as $value) {
                    $enrichedValues->removeValue($value);

                    if ($value instanceof ImageValue) {
                        $imageDataValue = $value->getValue();

                        if (!$imageDataValue) {
                            continue;
                        }

                        foreach (PreviewGeneratorRegistry::TYPES as $type) {
                            $this->previewGenerator->remove(
                                data: base64_encode($imageDataValue->getOriginalFilename()),
                                type: $type,
                            );
                        }

                        ($this->deleteFilesFromPaths)([$imageDataValue->getFilePath()]);
                    }
                }

                $cleanedEnrichedValues[$categoryCode] = $enrichedValues;
            }
        }

        if (!empty($cleanedEnrichedValues)) {
            $this->updateCategoryEnrichedValues->execute($cleanedEnrichedValues);
        }
    }
}

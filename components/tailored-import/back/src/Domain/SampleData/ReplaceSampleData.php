<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceSampleData
{
    public function fromExtractedColumn(array $extractedColumn, array $currentSample, int $indexToReplace): array
    {
        $cleanedExtract = self::removeAlreadyPickedSample($extractedColumn, $currentSample);
        $currentSample[$indexToReplace] = self::extractOneNewNonPickedValue($cleanedExtract);
        return self::fillBlankValues($currentSample);
    }

    private static function fillBlankValues(array $sampleData): array
    {
        return array_pad($sampleData, SelectSampleData::NUMBER_OF_VALUES, null);
    }

    private static function removeAlreadyPickedSample(array $extractedColumn, array $currentSample): array
    {
        return array_diff($extractedColumn, $currentSample);
    }

    private static function extractOneNewNonPickedValue(array $cleanedExtract): string|null
    {
        return current(SelectSampleData::fromExtractedColumn($cleanedExtract, 1));
    }
}

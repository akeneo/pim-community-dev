<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceSampleData
{
    public static function fromExtractedColumns(array $extractedColumns, array $currentSample): ?string
    {
        $formattedValues = FormatSampleData::format($extractedColumns);
        $cleanedExtract = self::removeAlreadyPickedSample($formattedValues, $currentSample);

        return self::extractOneNewUnpickedValue($cleanedExtract);
    }

    private static function removeAlreadyPickedSample(array $extractedValues, array $currentSample): array
    {
        return \array_diff($extractedValues, $currentSample);
    }

    private static function extractOneNewUnpickedValue(array $cleanedExtract): ?string
    {
        return \current(SelectSampleData::fromExtractedColumns([$cleanedExtract], 1));
    }
}

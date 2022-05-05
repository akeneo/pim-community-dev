<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

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

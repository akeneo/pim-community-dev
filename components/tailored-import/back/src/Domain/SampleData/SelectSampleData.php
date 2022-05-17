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

final class SelectSampleData
{
    public const NUMBER_OF_VALUES = 3;

    public static function fromExtractedColumns(array $extractedColumns, int $length = self::NUMBER_OF_VALUES): array
    {
        $formattedValues = FormatSampleData::format($extractedColumns);
        $uniqueValues = self::filterUniqueValues($formattedValues);
        $cleanedStringValue = self::replaceEmptyString($uniqueValues);
        $pickedValues = self::pickRandomValues($cleanedStringValue, $length);

        return self::fillBlankValues($pickedValues, $length);
    }

    private static function replaceEmptyString(array $sampleData): array
    {
        return \array_map(static fn ($value): ?string => 0 === strlen($value) ? null : $value, $sampleData);
    }

    private static function fillBlankValues(array $sampleData, int $length): array
    {
        return \array_pad($sampleData, $length, null);
    }

    private static function filterUniqueValues(array $sampleData): array
    {
        return \array_unique($sampleData);
    }

    private static function pickRandomValues(array $sampleData, int $length): array
    {
        \shuffle($sampleData);

        return \array_slice($sampleData, 0, \min(\count($sampleData), $length));
    }
}

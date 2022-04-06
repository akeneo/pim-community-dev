<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SelectSampleData
{
    public const NUMBER_OF_VALUES = 3;
    public const SAMPLE_DATA_MAX_LENGTH = 100;

    public static function fromExtractedColumn(array $extractedColumn, int $length = self::NUMBER_OF_VALUES): array
    {
        $uniqueValues = self::filterUniqueValues($extractedColumn);
        $pickedValues = self::pickRandomValues($uniqueValues, $length);
        $sampleData = self::formatValues($pickedValues);

        return self::fillBlankValues($sampleData, $length);
    }

    private static function fillBlankValues(array $sampleData, int $length): array
    {
        return \array_pad($sampleData, $length, null);
    }

    private static function filterUniqueValues(array $sampleData): array
    {
        return \array_unique($sampleData);
    }

    private static function formatValues(array $sampleData): array
    {
        return \array_map(
            static fn (string $value): string => \mb_substr(
                $value,
                0,
                self::SAMPLE_DATA_MAX_LENGTH,
            ),
            $sampleData,
        );
    }

    private static function pickRandomValues(array $sampleData, int $length): array
    {
        \shuffle($sampleData);

        return \array_slice($sampleData, 0, \min(\count($sampleData), $length));
    }
}

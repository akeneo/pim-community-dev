<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        return \array_map(static fn ($value): ?string => strlen($value) === 0 ? null : $value, $sampleData);
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

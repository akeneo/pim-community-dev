<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FormatSampleData
{
    public const SAMPLE_DATA_MAX_LENGTH = 101;

    public static function format(array $valuesIndexedByColumnIndex): array
    {
        $formattedValues = [];

        foreach ($valuesIndexedByColumnIndex as $values) {
            $formattedValues = [...$formattedValues, ...self::truncateValues($values)];
        }

        return $formattedValues;
    }

    /**
     * @param array<string|null> $values
     */
    private static function truncateValues(array $values): array
    {
        return \array_map(
            static fn (?string $value): ?string => null === $value ? null : \mb_substr(
                $value,
                0,
                self::SAMPLE_DATA_MAX_LENGTH,
            ),
            $values,
        );
    }
}

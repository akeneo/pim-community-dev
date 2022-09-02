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

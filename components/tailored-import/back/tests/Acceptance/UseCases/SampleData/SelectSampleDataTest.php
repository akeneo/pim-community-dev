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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\SampleData;

use Akeneo\Platform\TailoredImport\Domain\SampleData\FormatSampleData;
use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;
use PHPUnit\Framework\TestCase;

class SelectSampleDataTest extends TestCase
{
    public function test_it_selects_3_different_values_from_provided_columns(): void
    {
        $valuesIndexedByColumn = [
            1 => ['value1', 'value1', 'value2', 'value2', 'value3', 'value3'],
            3 => ['another1', 'another1', 'another2', 'another2', 'another3', 'another3'],
        ];
        $result = SelectSampleData::fromExtractedColumns($valuesIndexedByColumn);

        $this->assertCount(SelectSampleData::NUMBER_OF_VALUES, $result);
        $this->assertSameSize($result, array_unique($result));
    }

    public function test_it_completes_selection_with_null(): void
    {
        $valuesIndexedByColumn = [
            1 => ['value1'],
            3 => ['value2'],
        ];
        $result = SelectSampleData::fromExtractedColumns($valuesIndexedByColumn);

        $this->assertEqualsCanonicalizing(['value1', 'value2', null], $result);
    }

    public function test_it_selects_number_of_asked_values(): void
    {
        $valuesIndexedByColumn = [
            1 => ['value1', 'value1'],
            3 => ['value2'],
        ];
        $result = SelectSampleData::fromExtractedColumns($valuesIndexedByColumn, 2);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(['value1', 'value2'], $result);
    }

    public function test_it_truncates_every_long_values(): void
    {
        $column = [
            1 => [
                \str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH),
                \str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH + 1),
                \str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH + 10),
                'value2',
            ],
        ];
        $result = SelectSampleData::fromExtractedColumns($column);

        $this->assertEqualsCanonicalizing([
            \str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH),
            'value2',
            null,
        ], $result);
    }

    public function test_it_replace_empty_string_by_null(): void
    {
        $column = [
            1 => [
                "value1", "", "value3"
            ]
        ];

        $result = SelectSampleData::fromExtractedColumns($column);

        $this->assertEqualsCanonicalizing([
            null,
            "value1",
            "value3"
        ], $result);
    }
}

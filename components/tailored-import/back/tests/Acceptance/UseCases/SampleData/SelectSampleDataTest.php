<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\SampleData;

use Akeneo\Platform\TailoredImport\Domain\SampleData\FormatSampleData;
use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
}

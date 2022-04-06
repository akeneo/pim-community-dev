<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\SampleData;

use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectSampleDataTest extends TestCase
{
    public function test_it_selects_3_different_values_from_a_column(): void
    {
        $column = ['value1', 'value1', 'value2', 'value2', 'value3', 'value3'];
        $result = SelectSampleData::fromExtractedColumn($column);

        $this->assertCount(SelectSampleData::NUMBER_OF_VALUES, $result);
        $this->assertSameSize($result, array_unique($result));
    }

    public function test_it_completes_selection_with_null(): void
    {
        $column = ['value1', 'value2'];
        $result = SelectSampleData::fromExtractedColumn($column);

        $this->assertEqualsCanonicalizing(['value1', 'value2', null], $result);
    }

    public function test_it_selects_number_of_asked_values(): void
    {
        $column = ['value1', 'value1', 'value2'];
        $result = SelectSampleData::fromExtractedColumn($column, 2);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(['value1', 'value2'], $result);
    }

    public function test_it_truncates_every_long_values(): void
    {
        $column = [
            \str_repeat('a', SelectSampleData::SAMPLE_DATA_MAX_LENGTH),
            \str_repeat('a', SelectSampleData::SAMPLE_DATA_MAX_LENGTH + 1),
            \str_repeat('a', SelectSampleData::SAMPLE_DATA_MAX_LENGTH + 10),
            'value2',
        ];
        $result = SelectSampleData::fromExtractedColumn($column);

        foreach ($result as $sampleData) {
            $this->assertLessThanOrEqual(
                SelectSampleData::SAMPLE_DATA_MAX_LENGTH,
                \mb_strlen($sampleData),
            );
        };
    }
}

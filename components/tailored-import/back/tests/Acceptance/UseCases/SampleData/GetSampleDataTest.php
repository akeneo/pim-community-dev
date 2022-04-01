<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\SampleData;

use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetSampleDataTest extends TestCase
{
    public function test_it_select_3_different_value_from_a_column(): void
    {
        $sampleData = ["value1", "value1", "value2", "value2", "value3", "value3"];
        $result = SelectSampleData::fromExtractedColumn($sampleData);

        $this->assertCount(SelectSampleData::NUMBER_OF_VALUES, $result);
        $this->assertTrue(count($result) === count(array_unique($result)));
    }

    public function test_it_complete_selection_with_null(): void
    {
        $sampleData = ["value1", "value2"];
        $result = SelectSampleData::fromExtractedColumn($sampleData);

        $this->assertEqualsCanonicalizing(['value1', 'value2', null], $result);
    }

    public function test_it_select_number_of_asked_values(): void
    {
        $sampleData = ["value1", "value1", "value2"];
        $result = SelectSampleData::fromExtractedColumn($sampleData, 2);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(['value1', 'value2'], $result);
    }
}

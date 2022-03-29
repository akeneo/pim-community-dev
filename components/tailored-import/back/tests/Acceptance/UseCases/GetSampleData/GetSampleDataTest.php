<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\GetSampleData;

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
        $selectSampleData = new SelectSampleData();
        $sampleData = ["value1","value1","value2","value2","value3","value3"];
        $result = $selectSampleData->fromExtractedColumn($sampleData);

        $this->assertEquals(SelectSampleData::NUMBER_OF_VALUES, count($result));
        $this->assertTrue(count($result) === count(array_unique($result)));
    }

    public function test_it_complete_selection_with_null(): void
    {
        $selectSampleData = new SelectSampleData();
        $sampleData = ["value1","value2"];
        $result = $selectSampleData->fromExtractedColumn($sampleData);

        $this->assertTrue(in_array(null, $result));
        $this->assertTrue(in_array("value1", $result));
        $this->assertTrue(in_array("value2", $result));
    }
}

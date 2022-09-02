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
use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleData;
use PHPUnit\Framework\TestCase;

class ReplaceSampleDataTest extends TestCase
{
    public function test_it_replaces_sample_data_with_a_sample_data_not_already_present(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $valuesIndexedByColumn = [
            1 => ['value1', 'value2'],
            3 => ['another1', 'value3'],
        ];
        $currentSampleData = ['value1', 'value2', 'value3'];

        $result = $replaceSampleData->fromExtractedColumns($valuesIndexedByColumn, $currentSampleData);

        $this->assertEquals('another1', $result);
    }

    public function test_it_replaces_sample_data_with_null_when_sample_data_contain_all_column_values(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $valuesIndexedByColumn = [
            1 => ['value1', 'value2'],
            3 => ['another1'],
        ];
        $currentSampleData = ['value1', 'value2', 'another1'];

        $result = $replaceSampleData->fromExtractedColumns($valuesIndexedByColumn, $currentSampleData);

        $this->assertEquals(null, $result);
    }

    public function test_it_replaces_sample_data_with_a_sample_data_when_sample_data_contain_null(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $valuesIndexedByColumn = [
            1 => ['value1', 'value2'],
            3 => ['another1'],
        ];
        $currentSampleData = ['value1', 'value2', null];

        $result = $replaceSampleData->fromExtractedColumns($valuesIndexedByColumn, $currentSampleData);

        $this->assertEquals('another1', $result);
    }

    public function test_it_replaces_sample_data_with_a_truncated_sample_data(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $valuesIndexedByColumn = [
            1 => ['value1', 'value2'],
            3 => [\str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH + 1)],
        ];
        $currentSampleData = ['value1', 'value2', null];

        $result = $replaceSampleData->fromExtractedColumns($valuesIndexedByColumn, $currentSampleData);

        $this->assertEquals(\str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH), $result);
    }

    public function test_it_handles_truncated_current_sample_data(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $valuesIndexedByColumn = [
            1 => ['value1'],
            3 => [\str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH + 1)],
        ];
        $currentSampleData = [\str_repeat('a', FormatSampleData::SAMPLE_DATA_MAX_LENGTH), 'value1', null];

        $result = $replaceSampleData->fromExtractedColumns($valuesIndexedByColumn, $currentSampleData);

        $this->assertEquals(null, $result);
    }
}

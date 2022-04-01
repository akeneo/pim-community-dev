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

use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleData;
use PHPUnit\Framework\TestCase;

class ReplaceSampleDataTest extends TestCase
{
    public function test_it_replace_sample_data_with_a_sample_data_not_already_present(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $values = ['value1', 'value2', 'value3', 'value3', 'value4'];
        $currentSampleData = ['value1', 'value2', 'value3'];

        $result = $replaceSampleData->fromExtractedColumn($values, $currentSampleData);

        $this->assertEquals('value4', $result);
    }

    public function test_it_replace_sample_data_with_null_when_sample_data_contain_all_column_values(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $values = ['value1', 'value2', 'value3', 'value3'];
        $currentSampleData = ['value1', 'value2', 'value3'];

        $result = $replaceSampleData->fromExtractedColumn($values, $currentSampleData);

        $this->assertEquals(null, $result);
    }

    public function test_it_replace_sample_data_with_a_sample_data_when_sample_data_contain_null(): void
    {
        $replaceSampleData = new ReplaceSampleData();
        $values = ['value1', 'value2', 'value3', 'value3'];
        $currentSampleData = ['value1', 'value2', null];

        $result = $replaceSampleData->fromExtractedColumn($values, $currentSampleData);

        $this->assertEquals('value3', $result);
    }
}

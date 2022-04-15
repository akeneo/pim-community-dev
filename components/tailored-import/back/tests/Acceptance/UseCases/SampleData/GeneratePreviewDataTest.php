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

use Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData\GeneratePreviewDataHandler;
use Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData\GeneratePreviewDataQuery;
use Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData\GeneratePreviewDataResult;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;

class GeneratePreviewDataTest extends AcceptanceTestCase
{
    public function test_it_launch_operation_on_each_sample_data(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->operations = [['type' => 'clean_html_tags']];
        $query->sampleData = ['<b>product1</b>', '<i>product2</i>', 'product3'];

        $expected = GeneratePreviewDataResult::create(['product1', 'product2', 'product3']);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    public function test_it_does_nothing_when_there_is_no_operations(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->operations = [];
        $query->sampleData = ['<b>product1</b>', '<i>product2</i>', 'product3'];

        $expected = GeneratePreviewDataResult::create(['<b>product1</b>', '<i>product2</i>', 'product3']);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    public function test_it_does_nothing_when_there_is_no_sample_data(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->operations = [['type' => 'clean_html_tags']];
        $query->sampleData = [];

        $expected = GeneratePreviewDataResult::create([]);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    private function getHandler(): GeneratePreviewDataHandler
    {
        return self::getContainer()->get('akeneo.tailored_import.handler.sample_data.generate_preview_data_handler');
    }
}

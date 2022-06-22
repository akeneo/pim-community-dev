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
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;

class GeneratePreviewDataTest extends AcceptanceTestCase
{
    public function test_it_launches_operation_on_each_sample_data(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->target = [
            'code' => 'name',
            'type' => 'attribute',
            'attribute_type' => 'pim_catalog_text',
            'source_configuration' => null,
        ];
        $query->operations = [['uuid' => '00000000-0000-0000-0000-000000000000', 'type' => 'clean_html_tags']];
        $query->sampleData = ['<b>product1</b>', '<i>product2</i>', 'product3'];

        $expected = GeneratePreviewDataResult::create(['00000000-0000-0000-0000-000000000000' => [
            new StringValue('product1'),
            new StringValue('product2'),
            new StringValue('product3'),
        ]]);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    public function test_it_launches_multiple_operations_on_each_sample_data(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->target = [
            'code' => 'name',
            'type' => 'attribute',
            'attribute_type' => 'pim_catalog_multiselect',
            'source_configuration' => null,
        ];
        $query->operations = [
            [
                'uuid' => '00000000-0000-0000-0000-000000000000',
                'type' => 'split',
                'separator' => ';',
            ],
            [
                'uuid' => '00000000-0000-0000-0000-000000000001',
                'type' => 'multi_select_replacement',
                'mapping' => [
                    '1' => ['one', 'un', 'ein'],
                    '2' => ['two', 'deux', 'zwei'],
                ],
            ],
        ];
        $query->sampleData = ['one; two ;three ', null, 'un;deux;trois;quatre', 'ein    ;zwei'];

        $expected = GeneratePreviewDataResult::create(
            [
                '00000000-0000-0000-0000-000000000000' => [
                    new ArrayValue(['one', 'two', 'three']),
                    new NullValue(),
                    new ArrayValue(['un', 'deux', 'trois', 'quatre']),
                    new ArrayValue(['ein', 'zwei']),
                ],
                '00000000-0000-0000-0000-000000000001' => [
                    new ArrayValue(['1', '2', 'three']),
                    new NullValue(),
                    new ArrayValue(['1', '2', 'trois', 'quatre']),
                    new ArrayValue(['1', '2']),
                ],
            ],
        );
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    public function test_it_does_nothing_when_there_is_no_operations(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->target = [
            'code' => 'name',
            'type' => 'attribute',
            'attribute_type' => 'pim_catalog_text',
            'source_configuration' => null,
        ];
        $query->operations = [];
        $query->sampleData = ['<b>product1</b>', '<i>product2</i>', 'product3'];

        $expected = GeneratePreviewDataResult::create([]);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    public function test_it_does_nothing_when_there_is_no_sample_data(): void
    {
        $query = new GeneratePreviewDataQuery();
        $query->target = [
            'code' => 'name',
            'type' => 'attribute',
            'attribute_type' => 'pim_catalog_text',
            'source_configuration' => null,
        ];
        $query->operations = [['uuid' => '00000000-0000-0000-0000-000000000000', 'type' => 'clean_html_tags']];
        $query->sampleData = [];

        $expected = GeneratePreviewDataResult::create([]);
        $this->assertEquals($expected, $this->getHandler()->handle($query));
    }

    private function getHandler(): GeneratePreviewDataHandler
    {
        return self::getContainer()->get('akeneo.tailored_import.handler.sample_data.generate_preview_data_handler');
    }
}

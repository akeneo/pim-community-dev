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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\HandleDataMapping;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use PHPUnit\Framework\Assert;

final class HandleMeasurementTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_measurement_data_mapping_value(
        array $row,
        array $dataMappings,
        ExecuteDataMappingResult $expected,
    ): void {
        $executeDataMappingQuery = $this->getExecuteDataMappingQuery($row, '25621f5a-504f-4893-8f0c-9f1b0076e53e', $dataMappings);
        $result = $this->getExecuteDataMappingHandler()->handle($executeDataMappingQuery);

        Assert::assertEquals($expected, $result);
    }

    public function provider(): array
    {
        return [
            'it handles measurement attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '10',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => '60,5',
                    '25621f5a-504f-4893-8f0c-da684dfa84f7' => '6',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'size',
                            'pim_catalog_metric',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'unit' => 'METER',
                                'decimal_separator' => '.',
                            ],
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'weight',
                            'pim_catalog_metric',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            [
                                'unit' => 'GRAM',
                                'decimal_separator' => ',',
                            ],
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'frequency',
                            'pim_catalog_metric',
                            'ecommerce',
                            null,
                            'set',
                            'skip',
                            [
                                'unit' => 'HERTZ',
                                'decimal_separator' => ',',
                            ],
                        ),
                        ['25621f5a-504f-4893-8f0c-da684dfa84f7'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMeasurementValue('size', null, null, '10', 'METER'),
                            new SetMeasurementValue('weight', 'ecommerce', 'fr_FR', '60.5', 'GRAM'),
                            new SetMeasurementValue('frequency', 'ecommerce', null, '6', 'HERTZ'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles measurement attribute targets with invalid values' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '00000000-0000-0000-0000-000000000001' => '2022',
                    '00000000-0000-0000-0000-000000000002' => '12,5',
                    '00000000-0000-0000-0000-000000000003' => '6;5',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'size',
                            'pim_catalog_metric',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'unit' => 'METER',
                                'decimal_separator' => '.',
                            ],
                        ),
                        ['00000000-0000-0000-0000-000000000001'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'weight',
                            'pim_catalog_metric',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            [
                                'unit' => 'GRAM',
                                'decimal_separator' => '.',
                            ],
                        ),
                        ['00000000-0000-0000-0000-000000000002'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'frequency',
                            'pim_catalog_metric',
                            'ecommerce',
                            null,
                            'set',
                            'skip',
                            [
                                'unit' => 'HERTZ',
                                'decimal_separator' => ',',
                            ],
                        ),
                        ['00000000-0000-0000-0000-000000000003'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMeasurementValue('size', null, null, '2022', 'METER'),
                        ],
                    ),
                    [
                        new InvalidValue('Cannot convert "12,5" to a number with separator "."'),
                        new InvalidValue('Cannot convert "6;5" to a number with separator ","'),
                    ],
                ),
            ],
        ];
    }
}

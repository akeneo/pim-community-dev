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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use PHPUnit\Framework\Assert;

final class HandlePriceTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_price_data_mapping_value(
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
            'it handles price attribute targets' => [
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
                            'gross_price',
                            'pim_catalog_price_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'currency' => 'EUR',
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
                            'net_price',
                            'pim_catalog_price_collection',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            [
                                'currency' => 'USD',
                                'decimal_separator' => ',',
                            ],
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetPriceValue('gross_price', null, null, new PriceValue('10', 'EUR')),
                            new SetPriceValue('net_price', 'ecommerce', 'fr_FR', new PriceValue('60.5', 'USD')),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles multiple prices on the same attribute target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '10',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => '60',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'gross_price',
                            'pim_catalog_price_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'currency' => 'EUR',
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
                            'gross_price',
                            'pim_catalog_price_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'currency' => 'USD',
                                'decimal_separator' => '.',
                            ],
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetPriceValue('gross_price', null, null, new PriceValue('10', 'EUR')),
                            new SetPriceValue('gross_price', null, null, new PriceValue('60', 'USD')),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles price attribute targets with invalid values' => [
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
                            'gross_price',
                            'pim_catalog_price_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            [
                                'currency' => 'EUR',
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
                            'net_price',
                            'pim_catalog_price_collection',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            [
                                'currency' => 'USD',
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
                            'price',
                            'pim_catalog_price_collection',
                            'ecommerce',
                            null,
                            'set',
                            'skip',
                            [
                                'currency' => 'USD',
                                'decimal_separator' => ',',
                            ],
                        ),
                        ['00000000-0000-0000-0000-000000000003'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetPriceValue('gross_price', null, null, new PriceValue('2022', 'EUR')),
                        ],
                    ),
                    [
                        new InvalidValue('Cannot convert "12,5" to a number with separator "."'),
                        new InvalidValue('Cannot convert "6;5" to a number with separator ","'),
                    ],
                ),
            ],
            'it handles clear if empty price attribute target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'gross_price',
                            'pim_catalog_price_collection',
                            null,
                            null,
                            'set',
                            'clear',
                            [
                                'currency' => 'EUR',
                                'decimal_separator' => '.',
                            ],
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new ClearPriceValue('gross_price', null, null, 'EUR'),
                        ],
                    ),
                    [],
                ),
            ],
        ];
    }
}

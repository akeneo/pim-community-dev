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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

final class HandleMultiReferenceEntityTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_multi_reference_entity_data_mapping_value(
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
            'it handles multi reference entity attribute targets with single source and no operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'vneck,long_sleeve,sportwear',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'autumn_2021,summer_2022,winter_2022',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'akeneo_reference_entity_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'collection',
                            'akeneo_reference_entity_collection',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMultiReferenceEntityValue('tshirt_style', null, null, ['vneck,long_sleeve,sportwear']),
                            new SetMultiReferenceEntityValue('collection', 'ecommerce', 'fr_FR', ['autumn_2021,summer_2022,winter_2022']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles a multi reference entity attribute target with multiple sources and no operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'vneck',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'long_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'akeneo_reference_entity_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c', '2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMultiReferenceEntityValue('tshirt_style', null, null, ['vneck', 'long_sleeve']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles a multi reference entity attribute target with single source and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'long_sleeve,   short_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'akeneo_reference_entity_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation('00000000-0000-0000-0000-000000000000', ','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMultiReferenceEntityValue('tshirt_style', null, null, ['long_sleeve', 'short_sleeve']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles a multi reference entity attribute target with multiple sources and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'vneck',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'long_sleeve,short_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'akeneo_reference_entity_collection',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c', '2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation('00000000-0000-0000-0000-000000000000', ','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetMultiReferenceEntityValue('tshirt_style', null, null, ['vneck', 'long_sleeve', 'short_sleeve']),
                        ],
                    ),
                    [],
                ),
            ],
        ];
    }
}

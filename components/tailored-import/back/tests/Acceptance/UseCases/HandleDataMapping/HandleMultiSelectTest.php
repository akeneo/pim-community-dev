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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

final class HandleMultiSelectTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_multi_select_data_mapping_value(
        array $row,
        array $dataMappings,
        UpsertProductCommand $expected,
    ): void {
        $executeDataMappingQuery = $this->getExecuteDataMappingQuery($row, '25621f5a-504f-4893-8f0c-9f1b0076e53e', $dataMappings);
        $upsertProductCommand = $this->getExecuteDataMappingHandler()->handle($executeDataMappingQuery);

        Assert::assertEquals($expected, $upsertProductCommand);
    }

    public function provider(): array
    {
        return [
            'it handles multi select attribute targets with single source and no operation' => [
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
                            'pim_catalog_multiselect',
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
                            'pim_catalog_multiselect',
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
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['vneck,long_sleeve,sportwear']),
                        new SetMultiSelectValue('collection', 'ecommerce', 'fr_FR', ['autumn_2021,summer_2022,winter_2022']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with multiple sources and no operation' => [
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
                            'pim_catalog_multiselect',
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
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['vneck', 'long_sleeve']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with single source and replacement operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'pim_catalog_multiselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['11111111-1111-1111-1111-111111111111'],
                        OperationCollection::create([
                            new MultiSelectReplacementOperation([
                                'adidas' => ['vneck', 'long_sleeve'],
                                'puma' => ['short_sleeve'],
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['adidas']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with multiple sources and replacement operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                    '22222222-2222-2222-2222-222222222222' => 'long_sleeve',
                    '33333333-3333-3333-3333-333333333333' => 'short_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'pim_catalog_multiselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        [
                            '11111111-1111-1111-1111-111111111111',
                            '22222222-2222-2222-2222-222222222222',
                            '33333333-3333-3333-3333-333333333333',
                        ],
                        OperationCollection::create([
                            new MultiSelectReplacementOperation([
                                'adidas' => ['vneck', 'long_sleeve'],
                                'puma' => ['short_sleeve'],
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['adidas', 'puma']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with single source and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'long_sleeve,short_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'pim_catalog_multiselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation(','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['long_sleeve', 'short_sleeve']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with multiple sources and split operation' => [
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
                            'pim_catalog_multiselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c', '2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation(','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, ['vneck', 'long_sleeve', 'short_sleeve']),
                    ],
                ),
            ],
            'it handles a multi select attribute target with multiple sources and split & replacement operations' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                    '22222222-2222-2222-2222-222222222222' => 'short_sleeve,wide_sleeve,slim_fit',
                    '33333333-3333-3333-3333-333333333333' => 'boy,men,women',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'tshirt_style',
                            'pim_catalog_multiselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        [
                            '11111111-1111-1111-1111-111111111111',
                            '22222222-2222-2222-2222-222222222222',
                            '33333333-3333-3333-3333-333333333333',
                        ],
                        OperationCollection::create([
                            new SplitOperation(','),
                            new MultiSelectReplacementOperation([
                                'adidas' => ['slim_fit', 'wide_sleeve'],
                                'puma' => ['another_one', 'men'],
                                'broussaille' => ['boy'],
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMultiSelectValue('tshirt_style', null, null, [
                            'vneck',
                            'short_sleeve',
                            'adidas',
                            'broussaille',
                            'puma',
                            'women',
                        ]),
                    ],
                ),
            ],
        ];
    }
}

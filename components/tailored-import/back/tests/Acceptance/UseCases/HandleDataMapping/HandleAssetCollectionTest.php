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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

final class HandleAssetCollectionTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_an_asset_collection_data_mapping_value(
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
            'it handles an asset collection attribute targets with single source and no operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'this is an asset, this is another asset',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'asset1, asset2, asset3',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'an_asset',
                            'pim_catalog_asset_collection',
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
                            'another_asset',
                            'pim_catalog_asset_collection',
                            'ecommerce',
                            'fr_FR',
                            'add',
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
                            new SetAssetValue('an_asset', null, null, ['this is an asset, this is another asset']),
                            new AddAssetValue('another_asset', 'ecommerce', 'fr_FR', ['asset1, asset2, asset3']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles an asset collection attribute target with multiple sources and no operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'asset1',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'asset2',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'an_asset',
                            'pim_catalog_asset_collection',
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
                            new SetAssetValue('an_asset', null, null, ['asset1', 'asset2']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles an asset collection attribute target with single source and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'asset1,   asset2',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'an_asset',
                            'pim_catalog_asset_collection',
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
                            new SetAssetValue('an_asset', null, null, ['asset1', 'asset2']),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles an asset collection attribute target with multiple sources and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'asset1',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'asset2, asset3',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'an_asset',
                            'pim_catalog_asset_collection',
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
                            new SetAssetValue('an_asset', null, null, ['asset1', 'asset2', 'asset3']),
                        ],
                    ),
                    [],
                ),
            ],
            'it filters out empty values' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => '',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'an_asset',
                            'pim_catalog_asset_collection',
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
                        valueUserIntents: [],
                    ),
                    [],
                ),
            ],
        ];
    }
}

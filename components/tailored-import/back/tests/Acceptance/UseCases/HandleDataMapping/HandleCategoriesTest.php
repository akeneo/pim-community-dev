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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CategoriesReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use PHPUnit\Framework\Assert;

final class HandleCategoriesTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_data_mapping_targeting_categories(
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
            'it handles set categories targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'shoes',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'shoes, clothing',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new SetCategories(['shoes']),
                ),
            ],
            'it handles add categories targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'shoes',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'shoes, clothing',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'add',
                            'skip',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new AddCategories(['shoes']),
                ),
            ],
            'it handles a categories target with multiple sources' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'shoes',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'clothing',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c', '2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new SetCategories(['shoes', 'clothing']),
                ),
            ],
            'it handles a categories target with single source and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'shoes,clothing',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation('00000000-0000-0000-0000-000000000000', ','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new SetCategories(['shoes', 'clothing']),
                ),
            ],
            'it handles a categories target with multiple sources and split operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'shoes,women',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'shoes     ,clothing,    men ',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c', '2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SplitOperation('00000000-0000-0000-0000-000000000000', ','),
                        ]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new SetCategories(['shoes', 'women', 'clothing', 'men']),
                ),
            ],
            'it handles clear if empty on categories target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'clear',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    categoryUserIntent: new SetCategories([]),
                ),
            ],
            'it handles a categories target with single source and replacement operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        ['11111111-1111-1111-1111-111111111111'],
                        OperationCollection::create([
                            new CategoriesReplacementOperation('00000000-0000-0000-0000-000000000000', [
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
                    categoryUserIntent: new SetCategories(['adidas']),
                ),
            ],
            'it handles a categories target with multiple sources and replacement operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                    '22222222-2222-2222-2222-222222222222' => 'long_sleeve',
                    '33333333-3333-3333-3333-333333333333' => 'short_sleeve',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        [
                            '11111111-1111-1111-1111-111111111111',
                            '22222222-2222-2222-2222-222222222222',
                            '33333333-3333-3333-3333-333333333333',
                        ],
                        OperationCollection::create([
                            new CategoriesReplacementOperation('00000000-0000-0000-0000-000000000000', [
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
                    categoryUserIntent: new SetCategories(['adidas', 'puma']),
                ),
            ],
            'it handles a categories target with multiple sources and split & replacement operations' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'vneck',
                    '22222222-2222-2222-2222-222222222222' => 'short_sleeve,wide_sleeve,slim_fit',
                    '33333333-3333-3333-3333-333333333333' => 'boy,men,women',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'categories',
                            'set',
                            'skip',
                        ),
                        [
                            '11111111-1111-1111-1111-111111111111',
                            '22222222-2222-2222-2222-222222222222',
                            '33333333-3333-3333-3333-333333333333',
                        ],
                        OperationCollection::create([
                            new SplitOperation('00000000-0000-0000-0000-000000000000', ','),
                            new CategoriesReplacementOperation('00000000-0000-0000-0000-000000000000', [
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
                    categoryUserIntent: new SetCategories([
                        'vneck',
                        'short_sleeve',
                        'adidas',
                        'broussaille',
                        'puma',
                        'women',
                    ]),
                ),
            ],
        ];
    }
}

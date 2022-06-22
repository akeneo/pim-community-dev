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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

final class HandleSimpleSelectTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_simple_select_data_mapping_value(
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
            'it handles simple select attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'this is a brand',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'this is a color',
                ],
                'data_mappings' => [
                    $this->createIdentifierDataMapping('25621f5a-504f-4893-8f0c-9f1b0076e53e'),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'brand',
                            'pim_catalog_simpleselect',
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
                            'color',
                            'pim_catalog_simpleselect',
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
                            new SetSimpleSelectValue('brand', null, null, 'this is a brand'),
                            new SetSimpleSelectValue('color', 'ecommerce', 'fr_FR', 'this is a color'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles simple select attribute targets with replacement operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'nike',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'green',
                ],
                'data_mappings' => [
                    $this->createIdentifierDataMapping('25621f5a-504f-4893-8f0c-9f1b0076e53e'),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'brand',
                            'pim_catalog_simpleselect',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new SimpleSelectReplacementOperation('00000000-0000-0000-0000-000000000000', [
                                'adidas' => ['nike', 'reebok'],
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    new UpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        valueUserIntents: [
                            new SetSimpleSelectValue('brand', null, null, 'adidas'),
                        ],
                    ),
                    [],
                ),
            ],
        ];
    }
}

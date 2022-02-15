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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use PHPUnit\Framework\Assert;

final class HandleTextTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_text_data_mapping_value(
        array $row,
        array $dataMappings,
        UpsertProductCommand $expected,
    ): void {
        $executeDataMappingQuery = new ExecuteDataMappingQuery(
            new Row($row),
            DataMappingCollection::createFromNormalized($dataMappings),
        );

        $upsertProductCommand = $this->getExecuteDataMappingHandler()->handle($executeDataMappingQuery);

        Assert::assertEquals($expected, $upsertProductCommand);
    }

    public function provider(): array
    {
        return [
            'it handles text attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'this is a name',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'this is a description',
                ],
                'data_mappings' => [
                    [
                        'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
                        'target' => [
                            'type' => 'attribute',
                            'code' => 'sku',
                            'channel' => null,
                            'locale' => null,
                            'action' => 'set',
                            'if_empty' => 'skip',
                        ],
                        'sources' => ['25621f5a-504f-4893-8f0c-9f1b0076e53e'],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                    [
                        'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        'target' => [
                            'type' => 'attribute',
                            'code' => 'name',
                            'channel' => null,
                            'locale' => null,
                            'action' => 'set',
                            'if_empty' => 'skip',
                        ],
                        'sources' => ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                    [
                        'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        'target' => [
                            'type' => 'attribute',
                            'code' => 'description',
                            'channel' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'action' => 'set',
                            'if_empty' => 'skip',
                        ],
                        'sources' => ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valuesUserIntent: [
                        new SetTextValue('name', null, null, 'this is a name'),
                        new SetTextValue('description', 'fr_FR', 'ecommerce', 'this is a description'),
                    ],
                ),
            ],
        ];
    }
}

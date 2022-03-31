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

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use PHPUnit\Framework\Assert;

final class HandleTextareaTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_textarea_data_mapping_value(
        array $row,
        array $dataMappings,
        UpsertProductCommand $expected,
    ): void {
        $executeDataMappingQuery = new ExecuteDataMappingQuery(
            new Row($row),
            DataMappingCollection::create($dataMappings),
        );

        $upsertProductCommand = $this->getExecuteDataMappingHandler()->handle($executeDataMappingQuery);

        Assert::assertEquals($expected, $upsertProductCommand);
    }

    public function provider(): array
    {
        return [
            'it handles textarea attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'this is a textarea attribute',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'this is a description',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
                        TargetAttribute::create(
                            'sku',
                            'pim_catalog_identifier',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['25621f5a-504f-4893-8f0c-9f1b0076e53e'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        TargetAttribute::create(
                            'textarea_attribute',
                            'pim_catalog_textarea',
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
                        TargetAttribute::create(
                            'description',
                            'pim_catalog_textarea',
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
                        new SetTextareaValue('textarea_attribute', null, null, 'this is a textarea attribute'),
                        new SetTextareaValue('description', 'ecommerce', 'fr_FR', 'this is a description'),
                    ],
                ),
            ],
            'it handles text area attribute targets with Clean HTML Tags operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'i want&nbsp;this <h1>cleaned</h1>',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'but not <h2>this</h2>',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
                        TargetAttribute::create(
                            'sku',
                            'pim_catalog_identifier',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['25621f5a-504f-4893-8f0c-9f1b0076e53e'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        TargetAttribute::create(
                            'name',
                            'pim_catalog_textarea',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new CleanHTMLTagsOperation(),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        TargetAttribute::create(
                            'description',
                            'pim_catalog_textarea',
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
                        new SetTextareaValue('name', null, null, 'i want this cleaned'),
                        new SetTextareaValue('description', 'ecommerce', 'fr_FR', 'but not <h2>this</h2>'),
                    ],
                ),
            ],
        ];
    }
}

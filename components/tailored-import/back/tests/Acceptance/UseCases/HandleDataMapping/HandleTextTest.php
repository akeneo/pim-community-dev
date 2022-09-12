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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

final class HandleTextTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_text_data_mapping_value(
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
            'it handles text attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'this is a name',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'this is a description',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'name',
                            'pim_catalog_text',
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
                            'description',
                            'pim_catalog_text',
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
                    UpsertProductCommand::createWithIdentifier(
                        userId: 1,
                        productIdentifier: ProductIdentifier::fromIdentifier('this-is-a-sku'),
                        userIntents: [
                            new SetTextValue('name', null, null, 'this is a name'),
                            new SetTextValue('description', 'ecommerce', 'fr_FR', 'this is a description'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles text attribute targets with Clean HTML Tags operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'i want&nbsp;this <h1>cleaned</h1>',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'but not <h2>this</h2>',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'name',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new CleanHTMLTagsOperation('00000000-0000-0000-0000-000000000000'),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'description',
                            'pim_catalog_text',
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
                    UpsertProductCommand::createWithIdentifier(
                        userId: 1,
                        productIdentifier: ProductIdentifier::fromIdentifier('this-is-a-sku'),
                        userIntents: [
                            new SetTextValue('name', null, null, 'i want this cleaned'),
                            new SetTextValue('description', 'ecommerce', 'fr_FR', 'but not <h2>this</h2>'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles text attribute targets with Change Case operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'I need to be uppercased',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'I M FEELING TOO BIG',
                    '2d9e967a-3efa-4a31-a254-99f7c50a145c' => 'cette flûte n a qu un trou',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'name',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new ChangeCaseOperation('00000000-0000-0000-0000-000000000000', 'uppercase'),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'description',
                            'pim_catalog_text',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new ChangeCaseOperation('00000000-0000-0000-0000-000000000000', 'lowercase'),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fed',
                        AttributeTarget::create(
                            'fouras',
                            'pim_catalog_text',
                            'print',
                            'fr_FR',
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-3efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new ChangeCaseOperation('00000000-0000-0000-0000-000000000000', 'capitalize'),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    UpsertProductCommand::createWithIdentifier(
                        userId: 1,
                        productIdentifier: ProductIdentifier::fromIdentifier('this-is-a-sku'),
                        userIntents: [
                            new SetTextValue('name', null, null, 'I NEED TO BE UPPERCASED'),
                            new SetTextValue('description', 'ecommerce', 'fr_FR', 'i m feeling too big'),
                            new SetTextValue('fouras', 'print', 'fr_FR', 'Cette flûte n a qu un trou'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles text attribute targets with Remove Whitespace operation' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => ' A text with  whitespace  ',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => ' A text with  whitespace  ',
                    '2d9e967a-3efa-4a31-a254-99f7c50a145c' => ' A text with  whitespace  ',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'name1',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new RemoveWhitespaceOperation('00000000-0000-0000-0000-000000000000', ['consecutive']),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'name2',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new RemoveWhitespaceOperation('00000000-0000-0000-0000-000000000000', ['trim']),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fed',
                        AttributeTarget::create(
                            'name3',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-3efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new RemoveWhitespaceOperation('00000000-0000-0000-0000-000000000000', ['consecutive', 'trim']),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    UpsertProductCommand::createWithIdentifier(
                        userId: 1,
                        productIdentifier: ProductIdentifier::fromIdentifier('this-is-a-sku'),
                        userIntents: [
                            new SetTextValue('name1', null, null, ' A text with whitespace '),
                            new SetTextValue('name2', null, null, 'A text with  whitespace'),
                            new SetTextValue('name3', null, null, 'A text with whitespace'),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles text attribute targets with several operations' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => ' i want&nbsp;this trimmed, <h1>cleaned and capitalized</h1>',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => 'but not <h2>this</h2>',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'name',
                            'pim_catalog_text',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new CleanHTMLTagsOperation('00000000-0000-0000-0000-000000000000'),
                            new RemoveWhitespaceOperation('00000000-0000-0000-0000-000000000000', ['trim']),
                            new ChangeCaseOperation('00000000-0000-0000-0000-000000000000', 'capitalize'),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'description',
                            'pim_catalog_text',
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
                    UpsertProductCommand::createWithIdentifier(
                        userId: 1,
                        productIdentifier: ProductIdentifier::fromIdentifier('this-is-a-sku'),
                        userIntents: [
                            new SetTextValue('name', null, null, 'I want this trimmed, cleaned and capitalized'),
                            new SetTextValue('description', 'ecommerce', 'fr_FR', 'but not <h2>this</h2>'),
                        ],
                    ),
                    [],
                ),
            ],
        ];
    }
}

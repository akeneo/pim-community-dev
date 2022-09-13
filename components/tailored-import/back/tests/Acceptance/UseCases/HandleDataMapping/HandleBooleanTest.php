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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use PHPUnit\Framework\Assert;

final class HandleBooleanTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_boolean_data_mapping_value(
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
            'it handles boolean attribute target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '1',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145d' => '0',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'auto_exposure',
                            'pim_catalog_boolean',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new BooleanReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'auto_focus',
                            'pim_catalog_boolean',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145d'],
                        OperationCollection::create([
                            new BooleanReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetBooleanValue('auto_exposure', null, null, true),
                            new SetBooleanValue('auto_focus', 'ecommerce', 'fr_FR', false),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles boolean attribute target with invalid replacement value' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'nope',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145d' => '0',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'auto_exposure',
                            'pim_catalog_boolean',
                            null,
                            null,
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new BooleanReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'auto_focus',
                            'pim_catalog_boolean',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            null,
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145d'],
                        OperationCollection::create([
                            new BooleanReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetBooleanValue('auto_focus', 'ecommerce', 'fr_FR', false),
                        ],
                    ),
                    [
                        new InvalidValue('There is no mapped value for this source value: "nope"'),
                    ],
                ),
            ],
        ];
    }
}

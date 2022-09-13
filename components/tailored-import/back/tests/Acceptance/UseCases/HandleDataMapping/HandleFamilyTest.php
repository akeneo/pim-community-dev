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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use PHPUnit\Framework\Assert;

final class HandleFamilyTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_data_mapping_targeting_a_family(
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
            'it handles set family target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'a_family',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'family',
                            'set',
                            'skip',
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
                        userIntents: [new SetFamily('a_family')],
                    ),
                    [],
                ),
            ],
            'it handles clear if empty on family target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'family',
                            'set',
                            'clear',
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
                        userIntents: [new RemoveFamily()],
                    ),
                    [],
                ),
            ],
            'it handles family targets with a replacement operation without replaced value' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'a_family',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'family',
                            'set',
                            'clear',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new FamilyReplacementOperation('00000000-0000-0000-0000-000000000000', [
                                'my_family' => ['my awesome family']
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [new SetFamily('a_family')],
                    ),
                    [],
                ),
            ],
            'it handles family targets with a replacement operation with replaced value' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'my awesome family',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'family',
                            'set',
                            'clear',
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([
                            new FamilyReplacementOperation('00000000-0000-0000-0000-000000000000', [
                                'my_family' => ['my awesome family']
                            ]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [new SetFamily('my_family')],
                    ),
                    [],
                ),
            ],
        ];
    }
}

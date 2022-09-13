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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\EnabledReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use PHPUnit\Framework\Assert;

final class HandleEnabledTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_an_enabled_data_mapping_value(
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
            'it handles enable property target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => '1',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'enabled',
                            'set',
                            'skip',
                        ),
                        ['11111111-1111-1111-1111-111111111111'],
                        OperationCollection::create([
                            new EnabledReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [new SetEnabled(true)],
                    ),
                    [],
                ),
            ],
            'it handles disable property target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '00000000-0000-0000-0000-000000000000' => 'NON',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'enabled',
                            'set',
                            'skip',
                        ),
                        ['00000000-0000-0000-0000-000000000000'],
                        OperationCollection::create([
                            new EnabledReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['NON']]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [new SetEnabled(false)],
                    ),
                    [],
                ),
            ],
            'it handles enable property target with invalid replacement value' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '11111111-1111-1111-1111-111111111111' => 'nope',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        PropertyTarget::create(
                            'enabled',
                            'set',
                            'skip',
                        ),
                        ['11111111-1111-1111-1111-111111111111'],
                        OperationCollection::create([
                            new EnabledReplacementOperation('00000000-0000-0000-0000-000000000000', ['true' => ['1'], 'false' => ['0']]),
                        ]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    $this->createUpsertProductCommand(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: []
                    ),
                    [
                        new InvalidValue('There is no mapped value for this source value: "nope"'),
                    ],
                ),
            ],
        ];
    }
}

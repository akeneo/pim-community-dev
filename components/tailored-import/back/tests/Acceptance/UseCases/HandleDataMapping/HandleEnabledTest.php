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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use PHPUnit\Framework\Assert;

final class HandleEnabledTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_an_enabled_data_mapping_value(
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
            'it handles enable property targets' => [
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
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [],
                    enabledUserIntent: new SetEnabled(true),
                ),
            ],
            'it handles disable property targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '00000000-0000-0000-0000-000000000000' => '0',
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
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    enabledUserIntent: new SetEnabled(false),
                ),
            ]
        ];
    }
}

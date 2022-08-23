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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingResult;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use PHPUnit\Framework\Assert;

class HandleDateTest extends HandleDataMappingTestCase
{
    /**
     * @dataProvider provider
     */
    public function testItCanHandleADateDataMappingValue(
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
            'it handles date attribute target' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '02/22/2022',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145d' => '03/04/2022',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'release_date',
                            'pim_catalog_date',
                            null,
                            null,
                            'set',
                            'skip',
                            ['date_format' => 'mm/dd/yyyy'],
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        AttributeTarget::create(
                            'end_date',
                            'pim_catalog_date',
                            null,
                            null,
                            'set',
                            'skip',
                            ['date_format' => 'mm/dd/yyyy'],
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145d'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    UpsertProductCommand::createFromCollection(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [
                            new SetDateValue('release_date', null, null, \DateTimeImmutable::createFromFormat(
                                'Y-m-d\TH:i:s.uP',
                                '2022-02-22T00:00:00.000000+0000',
                                new \DateTimeZone('UTC'),
                            )),
                            new SetDateValue('end_date', null, null, \DateTimeImmutable::createFromFormat(
                                'Y-m-d\TH:i:s.uP',
                                '2022-03-04T00:00:00.000000+0000',
                                new \DateTimeZone('UTC'),
                            )),
                        ],
                    ),
                    [],
                ),
            ],
            'it handles date attribute target with invalid date' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '00000000-0000-0000-0000-000000000000' => 'this is not a date',
                ],
                'data_mappings' => [
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                        AttributeTarget::create(
                            'release_date',
                            'pim_catalog_date',
                            null,
                            null,
                            'set',
                            'skip',
                            ['date_format' => 'mm/dd/yyyy'],
                        ),
                        ['00000000-0000-0000-0000-000000000000'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new ExecuteDataMappingResult(
                    UpsertProductCommand::createFromCollection(
                        userId: 1,
                        productIdentifier: 'this-is-a-sku',
                        userIntents: [],
                    ),
                    [
                        new InvalidValue('Cannot format date "this is not a date" with provided format "mm/dd/yyyy"'),
                    ],
                ),
            ],
        ];
    }
}

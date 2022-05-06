<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HandleDateTest extends AttributeTestCase
{
    /**
     * @dataProvider provider("it handles date attribute targets")
     */
    public function testItCanHandleADateDataMappingValue(
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
            'it handles date attribute targets' => [
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
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
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
            ],
        ];
    }
}

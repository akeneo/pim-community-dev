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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use PHPUnit\Framework\Assert;

final class HandleMeasurementTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_handle_a_measurement_data_mapping_value(
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
            'it handles measurement attribute targets' => [
                'row' => [
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'this-is-a-sku',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => '10',
                    '2d9e967a-4efa-4a31-a254-99f7c50a145c' => '60,5',
                    '25621f5a-504f-4893-8f0c-da684dfa84f7' => '6',
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
                            'size',
                            'pim_catalog_metric',
                            null,
                            null,
                            'set',
                            'skip',
                            new MeasurementSourceParameter('METER', '.'),
                        ),
                        ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        TargetAttribute::create(
                            'weight',
                            'pim_catalog_metric',
                            'ecommerce',
                            'fr_FR',
                            'set',
                            'skip',
                            new MeasurementSourceParameter('GRAM', ',')
                        ),
                        ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                        OperationCollection::create([]),
                        [],
                    ),
                    DataMapping::create(
                        'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                        TargetAttribute::create(
                            'frequency',
                            'pim_catalog_metric',
                            'ecommerce',
                            null,
                            'set',
                            'skip',
                            new MeasurementSourceParameter('HERTZ', '.')
                        ),
                        ['25621f5a-504f-4893-8f0c-da684dfa84f7'],
                        OperationCollection::create([]),
                        [],
                    ),
                ],
                'expected' => new UpsertProductCommand(
                    userId: 1,
                    productIdentifier: 'this-is-a-sku',
                    valueUserIntents: [
                        new SetMetricValue('size', null, null, '10', 'METER'),
                        new SetMetricValue('weight', 'ecommerce', 'fr_FR', '60.5', 'GRAM'),
                        new SetMetricValue('frequency', 'ecommerce', null, '6', 'HERTZ'),
                    ],
                ),
            ],
        ];
    }
}

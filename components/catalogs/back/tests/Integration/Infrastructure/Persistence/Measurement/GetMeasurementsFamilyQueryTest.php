<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Measurement;

use Akeneo\Catalogs\Infrastructure\Persistence\Measurement\GetMeasurementsFamilyQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Measurement\GetMeasurementsFamilyQuery
 */
class GetMeasurementsFamilyQueryTest extends IntegrationTestCase
{
    private ?GetMeasurementsFamilyQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetMeasurementsFamilyQuery::class);
    }

    public function testItGetsMeasurementsFamily(): void
    {
        $result = $this->query->execute('Weight', 'en_US');

        $expected = [
            'code' => 'Weight',
            'units' => [
                [
                    'code' => 'MICROGRAM',
                    'label' => 'Microgram',
                    'convert_from_standard' => [
                        [
                            'value' => '0.000000001',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'MILLIGRAM',
                    'label' => 'Milligram',
                    'convert_from_standard' => [
                        [
                            'value' => '0.000001',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'GRAM',
                    'label' => 'Gram',
                    'convert_from_standard' => [
                        [
                            'value' => '0.001',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'KILOGRAM',
                    'label' => 'Kilogram',
                    'convert_from_standard' => [
                        [
                            'value' => '1',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'TON',
                    'label' => 'Ton',
                    'convert_from_standard' => [
                        [
                            'value' => '1000',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'GRAIN',
                    'label' => 'Grain',
                    'convert_from_standard' => [
                        [
                            'value' => '0.00006479891',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'DENIER',
                    'label' => 'Denier',
                    'convert_from_standard' => [
                        [
                            'value' => '0.001275',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'ONCE',
                    'label' => 'Once',
                    'convert_from_standard' => [
                        [
                            'value' => '0.03059',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'MARC',
                    'label' => 'Marc',
                    'convert_from_standard' => [
                        [
                            'value' => '0.24475',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'LIVRE',
                    'label' => 'Livre',
                    'convert_from_standard' => [
                        [
                            'value' => '0.4895',
                            'operator' => 'mul',
                        ],
                    ],
                ],
                [
                    'code' => 'OUNCE',
                    'label' => 'Ounce',
                    'convert_from_standard' => [
                        [
                            'value' => '0.45359237',
                            'operator' => 'mul',
                        ],
                        [
                            'value' => '16',
                            'operator' => 'div',
                        ],
                    ],
                ],
                [
                    'code' => 'POUND',
                    'label' => 'Pound',
                    'convert_from_standard' => [
                        [
                            'value' => '0.45359237',
                            'operator' => 'mul',
                        ],
                    ],
                ],
            ],
            'standard_unit' => 'KILOGRAM',
        ];

        self::assertEquals($expected, $result);
    }

    public function testItGetsNullWithInvalidCode(): void
    {
        $result = $this->query->execute('not_a_measurement_code', 'en_US');

        self::assertNull($result);
    }
}

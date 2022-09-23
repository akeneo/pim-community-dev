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
                ['code' => 'MICROGRAM', 'label' => 'Microgram'],
                ['code' => 'MILLIGRAM', 'label' => 'Milligram'],
                ['code' => 'GRAM', 'label' => 'Gram'],
                ['code' => 'KILOGRAM', 'label' => 'Kilogram'],
                ['code' => 'TON', 'label' => 'Ton'],
                ['code' => 'GRAIN', 'label' => 'Grain'],
                ['code' => 'DENIER', 'label' => 'Denier'],
                ['code' => 'ONCE', 'label' => 'Once'],
                ['code' => 'MARC', 'label' => 'Marc'],
                ['code' => 'LIVRE', 'label' => 'Livre'],
                ['code' => 'OUNCE', 'label' => 'Ounce'],
                ['code' => 'POUND', 'label' => 'Pound'],
            ],
        ];

        self::assertEquals($expected, $result);
    }

    public function testItGetsNullWithInvalidCode(): void
    {
        $result = $this->query->execute('not_a_measurement_code', 'en_US');

        self::assertNull($result);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\PublicApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\SqlGetUnit;

final class SqlGetUnitIntegration extends TestCase
{
    public function test_it_gets_unit_by_measurement_family_code_and_unit_code(): void
    {
        $query = $this->getQuery();

        $expected = [
            'code' => 'MICROGRAM',
            'labels' => [
                'en_US' => 'Microgram',
                'fr_FR' => 'Microgramme',
            ],
            'symbol' => 'μg',
            'convert_from_standard' => [
                [
                    'value' => '0.000000001',
                    'operator' => 'mul',
                ],
            ]
        ];
        $actual = $query->byMeasurementFamilyCodeAndUnitCode('Weight', 'MICROGRAM');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetUnit
    {
        return $this->get('akeneo_measurement.public_api.get_unit');
    }
}
